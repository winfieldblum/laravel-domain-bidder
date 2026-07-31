import { useOffers, useUpdateOfferStatus } from "@/hooks/use-offers";
import { useAuth } from "@/hooks/use-auth";
import { Button } from "@/components/ui/button";
import { 
  Table, 
  TableBody, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { format } from "date-fns";
import { Loader2, Check, X, LogIn } from "lucide-react";
import { Link } from "wouter";

export default function Admin() {
  const { user, isLoading: authLoading } = useAuth();
  const { data: offers, isLoading: offersLoading, error: offersError, status } = useOffers();
  const { mutate: updateStatus, isPending: isUpdating } = useUpdateOfferStatus();

  // Debug logging
  console.log("Admin page render - authLoading:", authLoading, "user:", user, "status:", status, "offersLoading:", offersLoading, "offers:", offers, "error:", offersError);

  if (authLoading) return <div className="flex h-screen items-center justify-center"><Loader2 className="animate-spin" /></div>;

  if (!user) {
    console.warn("User not authenticated, showing login prompt");
    return (
      <div className="h-[70vh] flex flex-col items-center justify-center gap-6">
        <h2 className="text-2xl font-bold text-slate-900">Admin Access Required</h2>
        <Link href="/login">
          <Button size="lg">
            <LogIn className="mr-2 h-4 w-4" />
            Log in with Replit
          </Button>
        </Link>
      </div>
    );
  }

  // Check if query is still loading
  if (status === "pending") {
    return <div className="flex h-screen items-center justify-center"><Loader2 className="animate-spin" /></div>;
  }

  // Check if user has admin access (API returns 403 if not authorized)
  if (offersError && ((offersError as any).message?.includes("403") || (offersError as any).message?.includes("Forbidden"))) {
    return (
      <div className="h-[70vh] flex flex-col items-center justify-center gap-6">
        <h2 className="text-2xl font-bold text-slate-900">Access Denied</h2>
        <p className="text-slate-600">You don't have admin permissions for this domain.</p>
        <p className="text-sm text-slate-500">Contact the domain owner for access.</p>
      </div>
    );
  }

  // Sort offers by amount desc
  const sortedOffers = (offers && Array.isArray(offers) ? [...offers] : []).sort((a, b) => b.amount - a.amount);

  return (
    <div className="container mx-auto py-12 px-4">
      <div className="flex justify-between items-center mb-8">
        <h1 className="text-3xl font-display font-bold text-slate-900">Offer Management</h1>
        <div className="text-sm text-slate-500">
          Total Offers: <span className="font-semibold text-slate-900">{sortedOffers.length}</span>
        </div>
      </div>

      <Card className="shadow-lg border-slate-200">
        <CardHeader className="bg-slate-50 border-b border-slate-100">
          <CardTitle className="text-lg">Incoming Bids</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          {offersLoading ? (
            <div className="p-12 flex justify-center text-slate-400">
              <Loader2 className="w-8 h-8 animate-spin" />
            </div>
          ) : sortedOffers.length === 0 ? (
            <div className="p-12 text-center text-slate-500">
              No offers yet.
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="w-[100px]">Date</TableHead>
                  <TableHead>Bidder</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead className="text-right">Amount</TableHead>
                  <TableHead className="text-center">Status</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {sortedOffers.map((offer) => (
                  <TableRow key={offer.id}>
                    <TableCell className="text-slate-500 text-sm whitespace-nowrap">
                      {format(new Date(offer.createdAt), "MMM d, yyyy")}
                    </TableCell>
                    <TableCell className="font-medium">{offer.name}</TableCell>
                    <TableCell className="text-slate-500">{offer.email}</TableCell>
                    <TableCell className="text-right font-mono font-medium text-slate-900">
                      ${offer.amount.toLocaleString()}
                    </TableCell>
                    <TableCell className="text-center">
                      <Badge 
                        variant={
                          offer.status === "accepted" ? "default" : 
                          offer.status === "rejected" ? "destructive" : "secondary"
                        }
                        className={offer.status === "accepted" ? "bg-green-600 hover:bg-green-700" : ""}
                      >
                        {offer.status}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-right">
                      {offer.status === "pending" && (
                        <div className="flex justify-end gap-2">
                          <Button
                            size="sm"
                            variant="outline"
                            className="text-green-600 hover:text-green-700 hover:bg-green-50 border-green-200"
                            onClick={() => updateStatus({ id: offer.id, status: "accepted" })}
                            disabled={isUpdating}
                          >
                            <Check className="w-4 h-4 mr-1" /> Accept
                          </Button>
                          <Button
                            size="sm"
                            variant="outline"
                            className="text-red-600 hover:text-red-700 hover:bg-red-50 border-red-200"
                            onClick={() => updateStatus({ id: offer.id, status: "rejected" })}
                            disabled={isUpdating}
                          >
                            <X className="w-4 h-4 mr-1" /> Reject
                          </Button>
                        </div>
                      )}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
