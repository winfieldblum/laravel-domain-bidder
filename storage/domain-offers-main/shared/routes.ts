import { z } from 'zod';
import { insertOfferSchema, offers, highestBidSchema } from './schema';

export const errorSchemas = {
  validation: z.object({
    message: z.string(),
    field: z.string().optional(),
  }),
  notFound: z.object({
    message: z.string(),
  }),
  internal: z.object({
    message: z.string(),
  }),
  conflict: z.object({
    message: z.string(),
  }),
};

export const api = {
  offers: {
    create: {
      method: 'POST' as const,
      path: '/api/offers',
      input: insertOfferSchema,
      responses: {
        201: z.custom<typeof offers.$inferSelect>(),
        400: errorSchemas.validation,
        409: errorSchemas.conflict,
      },
    },
    verify: {
      method: 'GET' as const,
      path: '/api/offers/verify/:token',
      responses: {
        200: z.custom<typeof offers.$inferSelect>(),
        400: errorSchemas.validation,
        404: errorSchemas.notFound,
      },
    },
    list: {
      method: 'GET' as const,
      path: '/api/offers',
      responses: {
        200: z.array(z.custom<typeof offers.$inferSelect>()),
        401: z.object({ message: z.string() }),
      },
    },
    updateStatus: {
      method: 'PATCH' as const,
      path: '/api/offers/:id/status',
      input: z.object({ status: z.enum(["pending", "accepted", "rejected"]) }),
      responses: {
        200: z.custom<typeof offers.$inferSelect>(),
        404: errorSchemas.notFound,
        401: z.object({ message: z.string() }),
      },
    },
    highest: {
      method: 'GET' as const,
      path: '/api/offers/highest',
      responses: {
        200: highestBidSchema,
      },
    },
  },
};

export function buildUrl(path: string, params?: Record<string, string | number>): string {
  let url = path;
  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      if (url.includes(`:${key}`)) {
        url = url.replace(`:${key}`, String(value));
      }
    });
  }
  return url;
}
