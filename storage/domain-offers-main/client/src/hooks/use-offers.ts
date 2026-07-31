import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { api, buildUrl } from "@shared/routes";
import { type InsertOffer, type Offer } from "@shared/schema";
import { useToast } from "@/hooks/use-toast";

export function useHighestBid() {
  return useQuery({
    queryKey: [api.offers.highest.path],
    queryFn: async () => {
      const res = await fetch(api.offers.highest.path);
      if (!res.ok) throw new Error("Failed to fetch highest bid");
      return api.offers.highest.responses[200].parse(await res.json());
    },
    // Refresh every 30 seconds to keep bid current
    refetchInterval: 30000,
  });
}

export function useOffers() {
  return useQuery({
    queryKey: [api.offers.list.path],
    queryFn: async () => {
      const res = await fetch(api.offers.list.path, { credentials: "include" });
      if (res.status === 401) throw new Error("Unauthorized");
      if (res.status === 403) {
        console.error("Admin access denied. Check your ADMIN_EMAILS environment variable.");
        throw new Error("Forbidden");
      }
      if (!res.ok) throw new Error("Failed to fetch offers");
      const data = await res.json();
      console.log("Offers data received:", data);
      const parsed = api.offers.list.responses[200].parse(data);
      console.log("Offers parsed successfully:", parsed);
      return parsed;
    },
  });
}

export function useCreateOffer() {
  const queryClient = useQueryClient();
  const { toast } = useToast();

  return useMutation({
    mutationFn: async (data: InsertOffer) => {
      const res = await fetch(api.offers.create.path, {
        method: api.offers.create.method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      });

      if (!res.ok) {
        if (res.status === 409) {
          throw new Error("Your offer must be higher than the current highest bid.");
        }
        if (res.status === 400) {
          const error = await res.json();
          throw new Error(error.message || "Invalid offer details.");
        }
        throw new Error("Failed to submit offer.");
      }
      return api.offers.create.responses[201].parse(await res.json());
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [api.offers.highest.path] });
      toast({
        title: "Offer Submitted",
        description: "We've received your offer. We'll be in touch shortly.",
      });
    },
    onError: (error: Error) => {
      toast({
        title: "Submission Failed",
        description: error.message,
        variant: "destructive",
      });
    },
  });
}

export function useUpdateOfferStatus() {
  const queryClient = useQueryClient();
  const { toast } = useToast();

  return useMutation({
    mutationFn: async ({ id, status }: { id: number; status: "accepted" | "rejected" | "pending" }) => {
      const url = buildUrl(api.offers.updateStatus.path, { id });
      const res = await fetch(url, {
        method: api.offers.updateStatus.method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ status }),
        credentials: "include",
      });

      if (!res.ok) throw new Error("Failed to update status");
      return api.offers.updateStatus.responses[200].parse(await res.json());
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [api.offers.list.path] });
      toast({
        title: "Status Updated",
        description: "The offer status has been changed.",
      });
    },
    onError: () => {
      toast({
        title: "Update Failed",
        description: "Could not update offer status. Try again.",
        variant: "destructive",
      });
    },
  });
}
