#!/bin/bash
# Multi-stage build for minimal production image with security hardening
# Stage 1: Builder using Bun for dependency installation and app building
FROM oven/bun:1.3-slim AS builder

# Set working directory
WORKDIR /app

# Copy only dependency files first for better layer caching
COPY package.json bun.lock* ./

# Install dependencies
# Use --frozen-lockfile equivalent for Bun for reproducible builds
RUN bun install --frozen-lockfile

# Copy source code and build
COPY . .

# Build the application
RUN bun run build

# Stage 2: Production using minimal nginx alpine with hardening
FROM nginx:alpine AS production

# Add metadata labels for better image management
LABEL maintainer="CTF Team"
LABEL description="Brooklyn 99 Heist - Frontend encrypted challenge"
LABEL version="1.0"

# Update nginx and security patches in one layer to reduce image size
RUN apk update && \
    apk upgrade && \
    rm -rf /var/cache/apk/* && \
    apk add --no-cache curl

# Note: nginx:alpine already includes the nginx user (uid: 101)
# We'll use the existing nginx user instead of creating a new one

# Copy nginx configuration (best practice: separate config file)
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Set proper permissions for nginx directories and files
RUN rm -rf /usr/share/nginx/html/* && \
    mkdir -p /usr/share/nginx/html && \
    chown -R nginx:nginx /usr/share/nginx/html && \
    chown -R nginx:nginx /var/cache/nginx && \
    chown -R nginx:nginx /var/log/nginx && \
    chown nginx:nginx /etc/nginx/conf.d/default.conf

# Copy built application from builder stage with correct permissions
COPY --from=builder --chown=nginx:nginx /app/dist /usr/share/nginx/html

# Verify nginx configuration is valid
RUN nginx -t

# Expose port 80
EXPOSE 80

# Health check to ensure container is running properly
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/index.html || exit 1

# Start nginx in foreground (required for PID 1)
CMD ["nginx", "-g", "daemon off;"]