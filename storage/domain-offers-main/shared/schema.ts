import { pgTable, text, serial, integer, timestamp, boolean } from "drizzle-orm/pg-core";
import { createInsertSchema } from "drizzle-zod";
import { z } from "zod";

// Export Replit Auth tables
export * from "./models/auth";

export const offers = pgTable("offers", {
  id: serial("id").primaryKey(),
  name: text("name").notNull(),
  email: text("email").notNull(),
  amount: integer("amount").notNull(),
  status: text("status", { enum: ["pending", "accepted", "rejected"] }).default("pending").notNull(),
  emailVerified: boolean("email_verified").default(false).notNull(),
  verificationToken: text("verification_token"),
  createdAt: timestamp("created_at").defaultNow().notNull(),
});

export const insertOfferSchema = createInsertSchema(offers)
  .omit({ 
    id: true, 
    status: true, 
    createdAt: true,
    emailVerified: true,
    verificationToken: true
  })
  .extend({
    name: z.string().min(2, "Name must be at least 2 characters").max(100, "Name is too long"),
    email: z.string().email("Please enter a valid email address"),
    amount: z.number().int("Amount must be a whole number").min(100, "Minimum bid is $100"),
  });

export type Offer = typeof offers.$inferSelect;
export type InsertOffer = z.infer<typeof insertOfferSchema>;

export const highestBidSchema = z.object({
  amount: z.number(),
});
