import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { LogIn } from "lucide-react";
import { useAuth } from "@/hooks/use-auth";
import { useLocation } from "wouter";
import { useEffect } from "react";

export default function Login() {
  const { user } = useAuth();
  const [, setLocation] = useLocation();

  useEffect(() => {
    if (user) {
      setLocation("/admin");
    }
  }, [user, setLocation]);

  const handleLogin = () => {
    window.location.href = "/api/login";
  };

  return (
    <div className="min-h-[80vh] flex items-center justify-center p-4">
      <Card className="w-full max-w-md shadow-xl">
        <CardHeader className="text-center space-y-2">
          <CardTitle className="text-2xl font-display">Owner Login</CardTitle>
          <CardDescription>
            Access the admin dashboard to manage offers
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Button 
            size="lg" 
            className="w-full h-12 text-lg" 
            onClick={handleLogin}
          >
            <LogIn className="mr-2 h-5 w-5" />
            Continue with Replit
          </Button>
        </CardContent>
      </Card>
    </div>
  );
}
