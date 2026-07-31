# agentic.io - Domain Sales Platform

## Project Overview
A minimalist domain sales platform for agentic.io that allows users to submit offers and lets the owner review and accept/reject bids.

## Key Features
- **Public Offer Page**: Users can view current highest bid and submit offers
- **Admin Dashboard**: Owner can review, accept, or reject offers
- **Replit Auth**: Secure authentication using Replit as OAuth provider
- **Email Notifications**: SendGrid integration for offer notifications

## Architecture
- **Frontend**: React + Shadcn UI components, Wouter routing, TanStack Query
- **Backend**: Express.js + TypeScript, PostgreSQL database
- **Auth**: Replit OpenID Connect (OIDC) via Passport.js
- **Email**: SendGrid (requires API key setup)

## Admin Access Control

### Setting Admin Users
Admin access is controlled via the `ADMIN_EMAILS` environment variable.

1. Go to the Secrets tab in Replit
2. Add an environment variable: `ADMIN_EMAILS`
3. Set the value to your email (or comma-separated list for multiple admins):
   ```
   chris@winfieldblum.com
   ```
   Or for multiple admins:
   ```
   chris@winfieldblum.com,admin2@example.com
   ```

Only users whose Replit email matches an entry in `ADMIN_EMAILS` can:
- View all offers via `/admin`
- Accept or reject bids
- Update offer statuses

Non-admin authenticated users will see "Access Denied" on the admin page.

## Email Notifications

### Setting Up SendGrid
To enable email notifications when offers are received:

1. Create a SendGrid account (free tier available)
2. Verify a sender email address (e.g., noreply@agentic.io)
3. Generate an API key from SendGrid dashboard
4. Add the API key to Replit Secrets: `SENDGRID_API_KEY`

Without the API key, offers will still be created but email notifications will be simulated (logged to console).

## Database Schema
- `users` - Replit auth users (managed by auth system)
- `sessions` - Express session storage (managed by auth system)
- `offers` - Domain sale offers with status (pending/accepted/rejected)

## API Endpoints
- `POST /api/offers` - Submit a new offer (public)
- `GET /api/offers/highest` - Get current highest bid (public)
- `GET /api/offers` - List all offers (admin only)
- `PATCH /api/offers/:id/status` - Update offer status (admin only)

## Deployment Notes
- Frontend served by Vite from http://0.0.0.0:5000
- Backend also on port 5000 (shared via Vite proxy)
- Uses PostgreSQL database from Neon
- Session storage via PostgreSQL with connect-pg-simple
