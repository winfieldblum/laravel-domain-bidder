import { storage } from "./storage";

export async function seedDatabase() {
  const existingOffers = await storage.getOffers();
  if (existingOffers.length === 0) {
    console.log("Seeding database with initial offers...");
    await storage.createOffer({
      name: "Alex Rivera",
      email: "alex@example.com",
      amount: 15000,
    });
    await storage.createOffer({
      name: "Sarah Chen",
      email: "sarah@techventures.com",
      amount: 22000,
    });
    await storage.createOffer({
      name: "Mike Ross",
      email: "mike@lawfirm.com",
      amount: 18500,
    });
    console.log("Seeding complete.");
  }
}
