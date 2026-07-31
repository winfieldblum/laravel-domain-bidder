import { Link, useLocation } from "wouter";
import { useAuth } from "@/hooks/use-auth";
import { Button } from "@/components/ui/button";
import { ShieldCheck, LogOut, ArrowRight } from "lucide-react";

export function Layout({ children }: { children: React.ReactNode }) {
  const [location] = useLocation();
  const { user, logout } = useAuth();

  const isHome = location === "/";

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col font-sans selection:bg-primary/20">
      <nav className="sticky top-0 z-50 w-full border-b border-gray-200 bg-white/80 backdrop-blur-md">
        <div className="container mx-auto px-4 h-16 flex items-center justify-between">
          <Link href="/" className="flex items-center gap-2 group cursor-pointer">
            <div className="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white font-bold text-lg group-hover:bg-accent transition-colors duration-300">
              A
            </div>
            <span className="font-display font-bold text-xl tracking-tight text-slate-900 group-hover:text-primary transition-colors">
              agentic.io
            </span>
          </Link>

          <div className="flex items-center gap-4">
            {user ? (
              <>
                <Link href="/admin">
                  <Button variant={location === "/admin" ? "secondary" : "ghost"} size="sm">
                    Dashboard
                  </Button>
                </Link>
                <Button 
                  variant="ghost" 
                  size="sm" 
                  onClick={() => logout()}
                  className="text-slate-500 hover:text-destructive"
                >
                  <LogOut className="w-4 h-4 mr-2" />
                  Logout
                </Button>
              </>
            ) : (
              <Link href="/login">
                <Button variant="ghost" size="sm" className="hidden md:flex text-slate-500">
                  <ShieldCheck className="w-4 h-4 mr-2" />
                  Owner Login
                </Button>
              </Link>
            )}
            
            {isHome && (
              <Link href="/offer">
                <Button className="font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                  Make Offer <ArrowRight className="w-4 h-4 ml-2" />
                </Button>
              </Link>
            )}
          </div>
        </div>
      </nav>

      <main className="flex-1 flex flex-col relative">
        {children}
      </main>

      <footer className="bg-white border-t border-slate-100 py-12">
        <div className="container mx-auto px-4 text-center">
          <p className="text-slate-500 text-sm mb-4">
            &copy; {new Date().getFullYear()} agentic.io. All rights reserved.
          </p>
          <div className="flex justify-center gap-6 text-sm text-slate-400">
            <span>Premium Domain</span>
            <span>Secure Transfer</span>
            <span>Escrow Available</span>
          </div>
        </div>
      </footer>
    </div>
  );
}
