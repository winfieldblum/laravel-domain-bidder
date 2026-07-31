import { useEffect, useState } from "react";
import { useRoute } from "wouter";
import { Loader2, CheckCircle2, XCircle } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Link } from "wouter";
import { motion } from "framer-motion";

type VerificationState = "loading" | "success" | "error";

export default function VerifyEmail() {
  const [match, params] = useRoute("/verify/:token");
  const [state, setState] = useState<VerificationState>("loading");
  const [offerAmount, setOfferAmount] = useState<number | null>(null);

  useEffect(() => {
    if (!match || !params?.token) return;

    const verifyEmail = async () => {
      try {
        const res = await fetch(`/api/offers/verify/${params.token}`);
        
        if (!res.ok) {
          setState("error");
          return;
        }

        const data = await res.json();
        setOfferAmount(data.amount);
        setState("success");
      } catch (error) {
        console.error("Verification failed:", error);
        setState("error");
      }
    };

    verifyEmail();
  }, [match, params?.token]);

  if (!match) return null;

  return (
    <div className="min-h-[80vh] flex items-center justify-center p-4">
      <motion.div
        initial={{ scale: 0.9, opacity: 0 }}
        animate={{ scale: 1, opacity: 1 }}
        className="max-w-md w-full text-center space-y-6"
      >
        {state === "loading" && (
          <>
            <div className="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <Loader2 className="w-10 h-10 text-blue-600 animate-spin" />
            </div>
            <h2 className="text-3xl font-display font-bold text-slate-900">
              Verifying Email
            </h2>
            <p className="text-slate-600 text-lg">
              Please wait while we confirm your email address...
            </p>
          </>
        )}

        {state === "success" && (
          <>
            <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <CheckCircle2 className="w-10 h-10 text-green-600" />
            </div>
            <h2 className="text-3xl font-display font-bold text-slate-900">
              Email Verified!
            </h2>
            <div className="space-y-2">
              <p className="text-slate-600 text-lg">
                Your offer has been confirmed.
              </p>
              {offerAmount && (
                <p className="text-slate-700 font-semibold">
                  Bid Amount: ${offerAmount.toLocaleString()}
                </p>
              )}
              <p className="text-slate-500 text-sm">
                The domain owner will review your bid shortly.
              </p>
            </div>
            <div className="pt-8">
              <Link href="/">
                <Button className="w-full">Return Home</Button>
              </Link>
            </div>
          </>
        )}

        {state === "error" && (
          <>
            <div className="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <XCircle className="w-10 h-10 text-red-600" />
            </div>
            <h2 className="text-3xl font-display font-bold text-slate-900">
              Verification Failed
            </h2>
            <p className="text-slate-600 text-lg">
              This verification link is invalid or has expired. Please submit a new offer.
            </p>
            <div className="pt-8">
              <Link href="/offer">
                <Button className="w-full">Submit New Offer</Button>
              </Link>
            </div>
          </>
        )}
      </motion.div>
    </div>
  );
}
