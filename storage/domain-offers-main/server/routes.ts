import type { Express } from "express";
import { createServer, type Server } from "http";
import { storage } from "./storage";
import { setupAuth, registerAuthRoutes } from "./replit_integrations/auth";
import { isAdmin } from "./replit_integrations/auth/admin-check";
import { api } from "@shared/routes";
import { getResendClient } from "./email";
import { z } from "zod";
import { randomBytes } from "crypto";

export async function registerRoutes(
  httpServer: Server,
  app: Express
): Promise<Server> {
  // Setup auth first
  setupAuth(app);
  registerAuthRoutes(app);

  // Highest bid (public endpoint)
  app.get(api.offers.highest.path, async (req, res) => {
    const amount = await storage.getHighestBid();
    res.json({ amount });
  });

  // Create offer (public endpoint)
  app.post(api.offers.create.path, async (req, res) => {
    try {
      const input = api.offers.create.input.parse(req.body);
      
      // Validate amount is higher than current highest
      const currentHighest = await storage.getHighestBid();
      if (input.amount <= currentHighest) {
        return res.status(409).json({ message: "Bid must be higher than the current highest bid." });
      }

      const verificationToken = randomBytes(32).toString("hex");
      const offer = await storage.createOffer({ ...input, verificationToken });

      // Send verification email
      const resendClient = await getResendClient();
      if (resendClient) {
        const verificationUrl = `${req.protocol}://${req.get("host")}/verify/${verificationToken}`;
        try {
          await resendClient.client.emails.send({
            from: `Agentic.io <${resendClient.fromEmail}>`,
            to: input.email,
            subject: "Verify your agentic.io offer",
            html: `
              <p>Thank you for your offer of $${input.amount} for agentic.io!</p>
              <p><a href="${verificationUrl}">Click here to verify your email</a></p>
              <p>Once verified, the domain owner will review your bid.</p>
            `,
          });
        } catch (emailError) {
          console.error("Failed to send verification email:", emailError);
        }
      }

      res.status(201).json(offer);
    } catch (err) {
      if (err instanceof z.ZodError) {
        return res.status(400).json({
          message: err.errors[0].message,
          field: err.errors[0].path.join('.'),
        });
      }
      throw err;
    }
  });

  // Verify email endpoint
  app.get(api.offers.verify.path, async (req, res) => {
    try {
      const token = req.params.token;
      const offer = await storage.verifyEmail(token);
      
      if (!offer) {
        return res.status(404).json({ message: "Verification link is invalid or expired." });
      }

      // Send notification to owner
      const notificationEmail = process.env.OFFER_NOTIFICATION_EMAIL || process.env.ADMIN_EMAIL;
      if (notificationEmail) {
        const resendClient = await getResendClient();
        if (resendClient) {
          try {
            await resendClient.client.emails.send({
              from: `Agentic.io <${resendClient.fromEmail}>`,
              to: notificationEmail,
              subject: `New Verified Offer: $${offer.amount} for agentic.io`,
              html: `
                <p>New verified offer received!</p>
                <p><strong>Amount:</strong> $${offer.amount}</p>
                <p><strong>From:</strong> ${offer.name} (${offer.email})</p>
                <p><a href="${req.protocol}://${req.get("host")}/admin">View in admin dashboard</a></p>
              `,
            });
          } catch (emailError) {
            console.error("Failed to send notification:", emailError);
          }
        }
      }

      res.json(offer);
    } catch (err) {
      console.error("Verification error:", err);
      res.status(500).json({ message: "Verification failed" });
    }
  });

  // Admin routes - protected by isAdmin middleware
  app.get(api.offers.list.path, isAdmin, async (req, res) => {
    const offers = await storage.getOffers();
    res.json(offers);
  });

  app.patch(api.offers.updateStatus.path, isAdmin, async (req, res) => {
    const id = parseInt(req.params.id);
    const { status } = req.body;
    
    const updated = await storage.updateOfferStatus(id, status);
    if (!updated) return res.status(404).json({ message: "Offer not found" });
    
    res.json(updated);
  });

  return httpServer;
}
