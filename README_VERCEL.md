# Vercel Frontend Deployment

This repo contains a static frontend version of the site that can be deployed on Vercel.

## What is deployed
- `index.html`
- `product-listing.html`
- static assets: `css/`, `js/`, `assets/`, `uploads/`

## What is not deployed
The Vercel deployment will not run PHP backend pages (`*.php` files) or server-side logic.

## Vercel config
The `vercel.json` file defines a static deployment for HTML pages.

## Deploy steps
1. Push the repo to GitHub.
2. In Vercel, import the repo.
3. Set the root directory to the repo root.
4. Use the default settings.
5. Deploy.

## Notes
- The static site may still include links or scripts targeting backend pages.
- For a fully working application, keep the backend deployed separately and point frontend calls to that backend.
