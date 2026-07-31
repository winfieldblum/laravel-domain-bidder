import type { RequestHandler } from "express";

/**
 * Middleware to check if authenticated user is an admin.
 * Uses ADMIN_EMAILS environment variable (comma-separated list of emails).
 * Example: ADMIN_EMAILS=chris@winfieldblum.com,admin2@example.com
 */
export const isAdmin: RequestHandler = async (req, res, next) => {
  const user = req.user as any;

  if (!req.isAuthenticated() || !user?.claims?.email) {
    return res.status(401).json({ message: "Unauthorized" });
  }

  const adminEmails = process.env.ADMIN_EMAILS?.split(",").map(e => e.trim()) || [];
  const userEmail = user.claims.email;
  
  console.log(`[Admin Check] User email: ${userEmail}, Admin emails: ${adminEmails.join(", ")}`);
  
  if (!adminEmails.includes(userEmail)) {
    console.log(`[Admin Check] Access denied for ${userEmail}`);
    return res.status(403).json({ message: "Forbidden: Admin access required" });
  }

  console.log(`[Admin Check] Access granted for ${userEmail}`);
  next();
};
