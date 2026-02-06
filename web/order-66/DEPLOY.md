# 🚀 Order 66 CTF - Deployment Guide

This guide provides step-by-step instructions for deploying the Order 66 CTF challenge application with nginx reverse proxy routing.

## 📋 Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Architecture Overview](#architecture-overview)
- [Detailed Deployment Steps](#detailed-deployment-steps)
- [Configuration](#configuration)
- [SSL/HTTPS Setup](#ssl-https-setup)
- [Testing & Verification](#testing--verification)
- [Troubleshooting](#troubleshooting)
- [Production Considerations](#production-considerations)

## 🔧 Prerequisites

### System Requirements
- **Docker**: Version 20.10+ 
- **Docker Compose**: Version 2.0+
- **Operating System**: Linux, macOS, or Windows with WSL2
- **Memory**: Minimum 2GB RAM
- **Storage**: Minimum 5GB free space

### Network Requirements
- Access to ports 80 and 443
- Internet connection for Docker image downloads
- Domain name (for production) or hosts file modification (for development)

### Required Tools
```bash
# Check Docker installation
docker --version
docker-compose --version

# Install required tools (Ubuntu/Debian)
sudo apt update
sudo apt install curl jq openssl

# Install required tools (macOS)
brew install curl jq openssl
```

## ⚡ Quick Start

For a quick deployment with default settings:

```bash
# 1. Clone or navigate to the project directory
cd /path/to/order-66

# 2. Add domain to hosts file (for local development)
echo "127.0.0.1 order66.skydays.ctf" | sudo tee -a /etc/hosts

# 3. Build and start all services
docker-compose up --build -d

# 4. Wait for services to initialize (30-60 seconds)
sleep 60

# 5. Access the application
open https://order66.skydays.ctf/
```

## 🏗️ Architecture Overview

The application consists of 4 main services:

```
┌─────────────────┐    ┌─────────────────┐
│   Browser       │───▶│  Nginx Proxy    │ Port 80/443
│                 │    │  (SSL/Routing)  │
└─────────────────┘    └─────────────────┘
                                │
                       ┌────────┴────────┐
                       ▼                 ▼
              ┌─────────────────┐ ┌─────────────────┐
              │  Vue.js         │ │  FastAPI        │
              │  Frontend       │ │  Backend        │ Port 8000
              │                 │ │                 │ (internal)
              └─────────────────┘ └─────────────────┘
                                          │
                                          ▼
                                 ┌─────────────────┐
                                 │  MySQL 8.0      │ Port 3306
                                 │  Database       │ (internal)
                                 └─────────────────┘
```

### Service Details

| Service | Technology | Port | Purpose |
|---------|------------|------|---------|
| **nginx** | Nginx 1.24 | 80/443 | Reverse proxy, SSL termination, routing |
| **frontend** | Vue.js 3 + Nginx | 80 (internal) | Single Page Application |
| **backend** | FastAPI + Python | 8000 (internal) | REST API, CTF logic |
| **mysql** | MySQL 8.0 | 3306 (internal) | Database storage |

## 📝 Detailed Deployment Steps

### Step 1: Environment Preparation

1. **Create project directory** (if deploying fresh):
```bash
mkdir order-66-ctf
cd order-66-ctf
# Copy all project files here
```

2. **Set up environment variables**:
```bash
# Copy example environment file
cp .env.example .env

# Edit environment variables (optional)
nano .env
```

3. **Configure domain name**:

For **development/local deployment**:
```bash
# Add to /etc/hosts (Linux/macOS)
echo "127.0.0.1 order66.skydays.ctf" | sudo tee -a /etc/hosts

# On Windows, edit C:\Windows\System32\drivers\etc\hosts
# Add line: 127.0.0.1 order66.skydays.ctf
```

For **production deployment**:
- Configure DNS A record pointing to your server IP
- Update domain in `nginx/conf.d/default.conf`
- Update `VITE_API_URL` in docker-compose.yml

### Step 2: SSL Certificate Generation

The application uses self-signed certificates by default. Generate them:

```bash
# Generate SSL certificates
./nginx/scripts/generate-certs.sh

# Verify certificates were created
ls -la nginx/ssl/
```

For **production**, replace with valid certificates:
```bash
# Copy your certificates
cp /path/to/your/cert.pem nginx/ssl/cert.pem
cp /path/to/your/key.pem nginx/ssl/key.pem

# Set proper permissions
chmod 644 nginx/ssl/cert.pem
chmod 600 nginx/ssl/key.pem
```

### Step 3: Build and Deploy

1. **Build all services**:
```bash
# Build with proper API URL
docker-compose build --build-arg VITE_API_URL=https://order66.skydays.ctf/api
```

2. **Start services**:
```bash
# Start in detached mode
docker-compose up -d

# Or start with logs (for debugging)
docker-compose up
```

3. **Verify services are running**:
```bash
# Check service status
docker-compose ps

# Expected output should show all services as healthy
```

### Step 4: Initialize Database

The database will initialize automatically with sample CTF data. If you need to reset:

```bash
# Stop services
docker-compose down

# Remove database volume
docker volume rm order66_mysql_data

# Restart services
docker-compose up -d
```

## ⚙️ Configuration

### Environment Variables

Key environment variables in `docker-compose.yml`:

```yaml
# Frontend Configuration
VITE_API_URL: https://order66.skydays.ctf/api  # API endpoint for frontend

# Backend Configuration
DATABASE_URL: mysql+pymysql://vader:deathstar@mysql:3306/empire_todos
FRONTEND_URL: https://order66.skydays.ctf
ALLOWED_ORIGINS: ["https://order66.skydays.ctf","http://order66.skydays.ctf"]

# Security
SECRET_KEY: execute-order-66-the-empire-strikes-back
CTF_FLAG: SKYDAYS{SKYORDER_66_HAD_TO_EXECUTED}

# Database
MYSQL_ROOT_PASSWORD: emperor
MYSQL_DATABASE: empire_todos
MYSQL_USER: vader
MYSQL_PASSWORD: deathstar
```

### Nginx Configuration

Main configuration files:
- `nginx/nginx.conf` - Main nginx config
- `nginx/conf.d/default.conf` - Server blocks and routing rules

Key routing rules:
```nginx
# API Backend (all /api/* requests)
location /api/ {
    proxy_pass http://backend:8000/api/;
    # ... proxy headers and settings
}

# Frontend (everything else)
location / {
    proxy_pass http://frontend;
    # ... SPA routing support
}
```

### Custom Domain Configuration

To use a different domain:

1. **Update nginx configuration**:
```bash
# Edit nginx/conf.d/default.conf
sed -i 's/order66.skydays.ctf/your-domain.com/g' nginx/conf.d/default.conf
```

2. **Update docker-compose.yml**:
```bash
# Update VITE_API_URL and FRONTEND_URL
sed -i 's/order66.skydays.ctf/your-domain.com/g' docker-compose.yml
```

3. **Regenerate SSL certificates**:
```bash
# Edit nginx/scripts/generate-certs.sh to use your domain
# Then run:
./nginx/scripts/generate-certs.sh
```

4. **Rebuild frontend**:
```bash
docker-compose build --no-cache frontend
docker-compose up -d
```

## 🔐 SSL/HTTPS Setup

### Self-Signed Certificates (Default)

Self-signed certificates are generated automatically:

```bash
# Generate new certificates
./nginx/scripts/generate-certs.sh

# Certificates are valid for 365 days
# Browser will show security warning (normal for self-signed)
```

### Production SSL Certificates

For production, use valid SSL certificates:

#### Option 1: Let's Encrypt (Recommended)

```bash
# Install certbot
sudo apt install certbot

# Generate certificate
sudo certbot certonly --standalone -d your-domain.com

# Copy certificates
sudo cp /etc/letsencrypt/live/your-domain.com/fullchain.pem nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/your-domain.com/privkey.pem nginx/ssl/key.pem

# Set permissions
sudo chown $USER:$USER nginx/ssl/*.pem
chmod 644 nginx/ssl/cert.pem
chmod 600 nginx/ssl/key.pem

# Restart nginx
docker-compose restart nginx
```

#### Option 2: Commercial Certificate

```bash
# Copy your certificates
cp /path/to/certificate.crt nginx/ssl/cert.pem
cp /path/to/private.key nginx/ssl/key.pem

# Set permissions
chmod 644 nginx/ssl/cert.pem
chmod 600 nginx/ssl/key.pem

# Restart nginx
docker-compose restart nginx
```

## ✅ Testing & Verification

### Automated Testing Script

Create a test script to verify deployment:

```bash
#!/bin/bash
# test-deployment.sh

DOMAIN="https://order66.skydays.ctf"

echo "🧪 Testing Order 66 CTF Deployment..."

# Test 1: HTTP to HTTPS redirect
echo "1. Testing HTTP to HTTPS redirect..."
REDIRECT=$(curl -s -I http://order66.skydays.ctf/ | grep -i location)
if [[ $REDIRECT == *"https"* ]]; then
    echo "   ✅ HTTP redirects to HTTPS"
else
    echo "   ❌ HTTP redirect failed"
fi

# Test 2: Frontend accessibility
echo "2. Testing frontend accessibility..."
FRONTEND=$(curl -k -s -I $DOMAIN/ | head -1)
if [[ $FRONTEND == *"200"* ]]; then
    echo "   ✅ Frontend accessible"
else
    echo "   ❌ Frontend not accessible"
fi

# Test 3: API health check
echo "3. Testing API health..."
API_HEALTH=$(curl -k -s $DOMAIN/api/health | jq -r '.status' 2>/dev/null)
if [[ $API_HEALTH == "operational" ]]; then
    echo "   ✅ API is operational"
else
    echo "   ❌ API health check failed"
fi

# Test 4: API endpoints
echo "4. Testing API endpoints..."
TODOS_COUNT=$(curl -k -s $DOMAIN/api/todos | jq '.todos | length' 2>/dev/null)
if [[ $TODOS_COUNT -gt 0 ]]; then
    echo "   ✅ API returns $TODOS_COUNT todos"
else
    echo "   ❌ API todos endpoint failed"
fi

# Test 5: CTF challenge endpoint
echo "5. Testing CTF challenge endpoint..."
CTF_INFO=$(curl -k -s $DOMAIN/api/info | jq -r '.challenge' 2>/dev/null)
if [[ $CTF_INFO == *"Order 66"* ]]; then
    echo "   ✅ CTF challenge endpoint working"
else
    echo "   ❌ CTF challenge endpoint failed"
fi

echo "🏁 Testing complete!"
```

### Manual Testing Steps

1. **Access the application**:
```bash
# Open in browser
open https://order66.skydays.ctf/

# Or test with curl
curl -k https://order66.skydays.ctf/
```

2. **Test API endpoints**:
```bash
# Health check
curl -k https://order66.skydays.ctf/api/health

# Get todos
curl -k https://order66.skydays.ctf/api/todos

# Get challenge info
curl -k https://order66.skydays.ctf/api/info
```

3. **Test CTF functionality**:
```bash
# Test SQL injection endpoint
curl -k "https://order66.skydays.ctf/api/todos?sort=name"
curl -k "https://order66.skydays.ctf/api/todos?sort=name' OR 1=1--"
```

### Health Monitoring

Monitor service health:

```bash
# Check all services
docker-compose ps

# Check specific service logs
docker-compose logs nginx
docker-compose logs frontend
docker-compose logs backend
docker-compose logs mysql

# Follow logs in real-time
docker-compose logs -f

# Check resource usage
docker stats
```

## 🔧 Troubleshooting

### Common Issues

#### 1. "Connection Refused" Error
```bash
# Check if services are running
docker-compose ps

# Check if domain is in hosts file
grep "order66.skydays.ctf" /etc/hosts

# Restart services
docker-compose restart
```

#### 2. "SSL Certificate Error"
```bash
# Regenerate certificates
./nginx/scripts/generate-certs.sh

# Restart nginx
docker-compose restart nginx

# For browsers: Click "Advanced" → "Proceed to site"
```

#### 3. "API Not Available" / "Offline Mode"
```bash
# Check if backend is healthy
docker-compose exec backend curl http://localhost:8000/api/health

# Rebuild frontend with correct API URL
docker-compose build --no-cache --build-arg VITE_API_URL=https://order66.skydays.ctf/api frontend
docker-compose up -d frontend

# Check API connectivity
curl -k https://order66.skydays.ctf/api/health
```

#### 4. Database Connection Issues
```bash
# Check database status
docker-compose exec mysql mysqladmin ping -h localhost -u root -pemperor

# Reset database
docker-compose down
docker volume rm order66_mysql_data
docker-compose up -d

# Check database logs
docker-compose logs mysql
```

#### 5. Frontend Shows Old Content
```bash
# Clear browser cache
# Or use incognito/private mode

# Rebuild frontend
docker-compose build --no-cache frontend
docker-compose up -d frontend
```

### Debugging Commands

```bash
# View all logs
docker-compose logs

# Check nginx configuration
docker-compose exec nginx nginx -t

# Test nginx reload
docker-compose exec nginx nginx -s reload

# Check container health
docker-compose exec frontend curl http://localhost:80/health
docker-compose exec backend curl http://localhost:8000/api/health

# Check network connectivity
docker-compose exec nginx ping frontend
docker-compose exec nginx ping backend
docker-compose exec backend ping mysql
```

### Performance Issues

```bash
# Check resource usage
docker stats

# Check disk space
df -h

# Monitor nginx access logs
docker-compose logs -f nginx | grep -v "GET /health"

# Check for memory leaks
docker-compose exec backend ps aux
```

## 🏭 Production Considerations

### Security Hardening

1. **Update default passwords**:
```bash
# Change database passwords in docker-compose.yml
MYSQL_ROOT_PASSWORD: <strong-password>
MYSQL_PASSWORD: <strong-password>
SECRET_KEY: <random-256-bit-key>
```

2. **Restrict network access**:
```bash
# Add firewall rules
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw deny 3306/tcp  # Block direct database access
sudo ufw deny 8000/tcp  # Block direct backend access
```

3. **Remove debug features**:
```bash
# Set production environment
DEBUG: "false"
ENVIRONMENT: "production"
```

### Performance Optimization

1. **Enable additional caching**:
```nginx
# Add to nginx/conf.d/default.conf
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

2. **Database optimization**:
```bash
# Increase buffer sizes for production
# Edit docker-compose.yml mysql command:
--innodb-buffer-pool-size=512M
--max-connections=200
```

3. **Enable log rotation**:
```bash
# Add log rotation for nginx
# Create /etc/logrotate.d/nginx-docker
```

### Backup Strategy

```bash
# Database backup
docker-compose exec mysql mysqldump -u root -pemperor empire_todos > backup.sql

# Full application backup
tar -czf order-66-backup.tar.gz \
    docker-compose.yml \
    nginx/ \
    frontend/ \
    backend/ \
    .env
```

### Monitoring Setup

```bash
# Add health check endpoints to monitoring
GET https://order66.skydays.ctf/nginx-health
GET https://order66.skydays.ctf/api/health

# Monitor key metrics:
# - Response time
# - Error rate
# - SSL certificate expiry
# - Disk space usage
# - Memory usage
```

### High Availability

For production deployments:

1. **Load balancer setup**
2. **Database replication**
3. **Container orchestration** (Kubernetes/Docker Swarm)
4. **Auto-scaling configuration**
5. **Disaster recovery plan**

## 📚 Additional Resources

- **Project Documentation**: `nginx/README.md`
- **API Documentation**: Access `/docs` endpoint after deployment
- **CTF Challenge Guide**: Check `/api/info` for challenge hints
- **Docker Compose Reference**: [docs.docker.com](https://docs.docker.com/compose/)
- **Nginx Configuration**: [nginx.org/en/docs](https://nginx.org/en/docs/)

## 🆘 Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review service logs: `docker-compose logs [service-name]`
3. Verify all prerequisites are met
4. Test individual components separately
5. Check for port conflicts with other services

---

**Happy Hacking! May the Force be with you! 🌟**

*"Execute Order 66 - Deploy the Empire's finest CTF challenge!"*