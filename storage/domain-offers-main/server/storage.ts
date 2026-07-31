import { db } from "./db";
import {
  users, offers,
  type User, type InsertUser,
  type Offer, type InsertOffer
} from "@shared/schema";
import { eq, desc, max, not } from "drizzle-orm";

export interface IStorage {
  getUser(id: number): Promise<User | undefined>;
  getUserByUsername(username: string): Promise<User | undefined>;
  createUser(user: InsertUser): Promise<User>;
  
  createOffer(offer: InsertOffer & { verificationToken: string }): Promise<Offer>;
  getOffers(): Promise<Offer[]>;
  getOffer(id: number): Promise<Offer | undefined>;
  updateOfferStatus(id: number, status: "pending" | "accepted" | "rejected"): Promise<Offer | undefined>;
  verifyEmail(token: string): Promise<Offer | undefined>;
  getHighestBid(): Promise<number>;
}

export class DatabaseStorage implements IStorage {
  async getUser(id: number): Promise<User | undefined> {
    const [user] = await db.select().from(users).where(eq(users.id, id));
    return user;
  }

  async getUserByUsername(username: string): Promise<User | undefined> {
    const [user] = await db.select().from(users).where(eq(users.username, username));
    return user;
  }

  async createUser(insertUser: InsertUser): Promise<User> {
    const [user] = await db.insert(users).values(insertUser).returning();
    return user;
  }

  async createOffer(offer: InsertOffer): Promise<Offer> {
    const [newOffer] = await db.insert(offers).values(offer).returning();
    return newOffer;
  }

  async getOffers(): Promise<Offer[]> {
    return await db.select().from(offers).orderBy(desc(offers.amount));
  }

  async getOffer(id: number): Promise<Offer | undefined> {
    const [offer] = await db.select().from(offers).where(eq(offers.id, id));
    return offer;
  }

  async updateOfferStatus(id: number, status: "pending" | "accepted" | "rejected"): Promise<Offer | undefined> {
    const [updated] = await db.update(offers)
      .set({ status })
      .where(eq(offers.id, id))
      .returning();
    return updated;
  }

  async verifyEmail(token: string): Promise<Offer | undefined> {
    const [offer] = await db
      .update(offers)
      .set({ emailVerified: true, verificationToken: null })
      .where(eq(offers.verificationToken, token))
      .returning();
    return offer;
  }

  async getHighestBid(): Promise<number> {
    const [result] = await db
      .select({ maxAmount: max(offers.amount) })
      .from(offers)
      .where(eq(offers.status, "accepted"));
    return result?.maxAmount ?? 0;
  }
}

export const storage = new DatabaseStorage();
