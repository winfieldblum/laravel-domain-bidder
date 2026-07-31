import { Link } from "wouter";
import { Button } from "@/components/ui/button";
import { useHighestBid } from "@/hooks/use-offers";
import { ArrowRight, CheckCircle2, TrendingUp, Shield, Globe } from "lucide-react";
import { motion } from "framer-motion";

export default function Home() {
  const { data: highestBid, isLoading } = useHighestBid();

  const features = [
    {
      icon: <Globe className="w-6 h-6 text-blue-500" />,
      title: "Category Defining",
      description: "Own the authoritative domain for the autonomous agent revolution."
    },
    {
      icon: <TrendingUp className="w-6 h-6 text-green-500" />,
      title: "High Valuation",
      description: ".io domains are the gold standard for tech startups and AI platforms."
    },
    {
      icon: <Shield className="w-6 h-6 text-purple-500" />,
      title: "Brand Authority",
      description: "Instant credibility with a name that defines the future of software."
    }
  ];

  return (
    <div className="flex flex-col">
      {/* Hero Section */}
      <section className="relative py-20 lg:py-32 overflow-hidden">
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-100/50 via-slate-50 to-white -z-10" />
        
        <div className="container mx-auto px-4">
          <div className="max-w-4xl mx-auto text-center">
            <motion.div 
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5 }}
            >
              <span className="inline-block px-4 py-1.5 rounded-full bg-indigo-50 text-indigo-700 font-medium text-sm mb-6 border border-indigo-100">
                Premium Domain For Sale
              </span>
              <h1 className="text-6xl md:text-8xl font-display font-bold tracking-tighter text-slate-900 mb-6">
                agentic.io
              </h1>
              <p className="text-xl md:text-2xl text-slate-600 mb-10 leading-relaxed max-w-2xl mx-auto text-balance">
                The perfect identity for your AI agency, autonomous agent platform, or next-gen software company.
              </p>
            </motion.div>

            <motion.div 
              initial={{ opacity: 0, scale: 0.95 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: 0.2, duration: 0.5 }}
              className="glass-card p-8 md:p-10 max-w-md mx-auto mb-12"
            >
              <div className="text-sm text-slate-500 font-medium uppercase tracking-wider mb-2">
                Current Highest Offer
              </div>
              <div className="text-5xl font-display font-bold text-slate-900 mb-2 tabular-nums">
                {isLoading ? (
                  <span className="animate-pulse bg-slate-200 rounded h-12 w-32 inline-block"/>
                ) : (
                  new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(highestBid?.amount || 0)
                )}
              </div>
              <p className="text-slate-500 text-sm mb-6">USD</p>
              
              <Link href="/offer">
                <Button size="lg" className="w-full text-lg h-14 bg-primary hover:bg-primary/90 shadow-lg shadow-indigo-500/20">
                  Place Your Bid <ArrowRight className="ml-2 w-5 h-5" />
                </Button>
              </Link>
              <p className="text-xs text-slate-400 mt-4">
                Secure transaction via Escrow.com available
              </p>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Value Props */}
      <section className="py-24 bg-white border-y border-slate-100">
        <div className="container mx-auto px-4">
          <div className="grid md:grid-cols-3 gap-12">
            {features.map((feature, i) => (
              <motion.div 
                key={i}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.1 }}
                className="text-center px-4"
              >
                <div className="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-6 border border-slate-100">
                  {feature.icon}
                </div>
                <h3 className="text-xl font-bold mb-3">{feature.title}</h3>
                <p className="text-slate-600 leading-relaxed">{feature.description}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Trust Indicators */}
      <section className="py-20 bg-slate-50">
        <div className="container mx-auto px-4 text-center">
          <h3 className="text-2xl font-display font-bold mb-10">Why acquire this domain?</h3>
          <div className="max-w-3xl mx-auto grid gap-6 text-left">
            {[
              "Short, memorable, and easy to spell",
              "Directly relates to 'Agentic AI' - the hottest trend in tech",
              ".io extension is preferred by developer tools and SaaS platforms",
              "Instant SEO advantage for agent-related keywords"
            ].map((item, i) => (
              <div key={i} className="flex items-center p-4 bg-white rounded-xl border border-slate-200 shadow-sm">
                <CheckCircle2 className="w-6 h-6 text-primary mr-4 flex-shrink-0" />
                <span className="text-lg text-slate-800">{item}</span>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
