import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useCreateOffer, useHighestBid } from "@/hooks/use-offers";
import { insertOfferSchema, type InsertOffer } from "@shared/schema";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Form, FormControl, FormField, FormItem, FormMessage } from "@/components/ui/form";
import { ArrowLeft, Loader2, DollarSign } from "lucide-react";
import { Link, useLocation } from "wouter";
import { motion } from "framer-motion";
import { useEffect } from "react";

export default function MakeOffer() {
  const [, setLocation] = useLocation();
  const { data: highestBid } = useHighestBid();
  const { mutate: createOffer, isPending, isSuccess } = useCreateOffer();

  const minBid = (highestBid?.amount || 0) + 100;

  const form = useForm<InsertOffer>({
    resolver: zodResolver(insertOfferSchema),
    defaultValues: {
      name: "",
      email: "",
      amount: minBid,
    },
  });

  // Update min bid requirement when data loads
  useEffect(() => {
    if (highestBid?.amount) {
      const currentAmount = form.getValues("amount");
      if (currentAmount <= highestBid.amount) {
        form.setValue("amount", highestBid.amount + 100);
      }
    }
  }, [highestBid, form]);

  const onSubmit = (data: InsertOffer) => {
    createOffer(data);
  };

  if (isSuccess) {
    return (
      <div className="min-h-[80vh] flex items-center justify-center p-4">
        <motion.div 
          initial={{ scale: 0.9, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          className="max-w-md w-full text-center space-y-6"
        >
          <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <DollarSign className="w-10 h-10 text-green-600" />
          </div>
          <h2 className="text-3xl font-display font-bold text-slate-900">Offer Received!</h2>
          <p className="text-slate-600 text-lg">
            Thank you for your offer! Check your email for a verification link from Agentic.io. Once confirmed, the domain owner will review your bid.
          </p>
          <div className="pt-8">
            <Link href="/">
              <Button variant="outline" className="w-full">Return Home</Button>
            </Link>
          </div>
        </motion.div>
      </div>
    );
  }

  return (
    <div className="container max-w-lg mx-auto py-12 px-4">
      <Link href="/" className="inline-flex items-center text-slate-500 hover:text-slate-900 mb-8 transition-colors">
        <ArrowLeft className="w-4 h-4 mr-2" /> Back to home
      </Link>

      <motion.div
        initial={{ y: 20, opacity: 0 }}
        animate={{ y: 0, opacity: 1 }}
        transition={{ duration: 0.4 }}
      >
        <Card className="border-slate-200 shadow-xl shadow-slate-200/50">
          <CardHeader className="space-y-1 pb-8 border-b border-slate-50 bg-slate-50/50 rounded-t-xl">
            <CardTitle className="text-2xl font-display">Make an Offer</CardTitle>
            <CardDescription className="text-base">
              Current highest bid: <span className="font-semibold text-slate-900">${highestBid?.amount?.toLocaleString() ?? 0} USD</span>
            </CardDescription>
          </CardHeader>
          <CardContent className="pt-8">
            <Form {...form}>
              <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
                <FormField
                  control={form.control}
                  name="name"
                  render={({ field }) => (
                    <FormItem>
                      <Label className="text-slate-700">Full Name</Label>
                      <FormControl>
                        <Input placeholder="John Doe" className="h-11" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={form.control}
                  name="email"
                  render={({ field }) => (
                    <FormItem>
                      <Label className="text-slate-700">Email Address</Label>
                      <FormControl>
                        <Input placeholder="john@company.com" type="email" className="h-11" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={form.control}
                  name="amount"
                  render={({ field }) => (
                    <FormItem>
                      <Label className="text-slate-700">Offer Amount (USD)</Label>
                      <FormControl>
                        <div className="relative">
                          <DollarSign className="absolute left-3 top-3 h-5 w-5 text-slate-400" />
                          <Input 
                            type="number" 
                            className="pl-10 h-11 text-lg font-medium" 
                            {...field}
                            onChange={(e) => field.onChange(Number(e.target.value))}
                          />
                        </div>
                      </FormControl>
                      <p className="text-xs text-slate-500 mt-2">
                        Minimum bid: ${(minBid).toLocaleString()} USD
                      </p>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <Button 
                  type="submit" 
                  className="w-full h-12 text-lg bg-primary hover:bg-primary/90 mt-4"
                  disabled={isPending}
                >
                  {isPending ? (
                    <>
                      <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                      Submitting...
                    </>
                  ) : (
                    "Submit Offer"
                  )}
                </Button>
              </form>
            </Form>
          </CardContent>
        </Card>
      </motion.div>
    </div>
  );
}
