import type { Express } from "express";
import { isAuthenticated } from "./replitAuth";

// Register auth-specific routes
export function registerAuthRoutes(app: Express): void {
  // Get current authenticated user
  app.get("/api/auth/user", isAuthenticated, async (req: any, res) => {
    try {
      // Return the user data directly from the session
      const user = req.user;
      if (!user || !user.claims) {
        return res.status(401).json({ message: "Not authenticated" });
      }
      res.json({
        id: user.claims.sub,
        email: user.claims.email,
        firstName: user.claims.first_name,
        lastName: user.claims.last_name,
        profileImageUrl: user.claims.profile_image_url,
      });
    } catch (error) {
      console.error("Error fetching user:", error);
      res.status(500).json({ message: "Failed to fetch user" });
    }
  });
}
