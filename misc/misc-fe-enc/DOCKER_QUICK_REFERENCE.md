# Docker Quick Reference Guide

## 🚀 Essential Commands

### Build & Run
```bash
# Copy environment file
cp .env.example .env

# Build image
docker-compose build

# Start container (background)
docker-compose up -d

# Start with logs visible
docker-compose up

# Stop container
docker-compose stop

# Stop and remove
docker-compose down

# Rebuild without cache
docker-compose build --no-cache
```

### Monitoring & Debugging
```bash
# Check container status
docker-compose ps

# View logs (last 50 lines)
docker-compose logs app-prod --tail=50

# Follow logs in real-time
docker-compose logs -f app-prod

# View resource usage
docker stats client-site-enc-prod

# Inspect detailed info
docker inspect client-site-enc-prod

# Check health status
docker ps --filter "name=client-site-enc-prod"
```

### Container Access
```bash
# Get shell access
docker exec -it client-site-enc-prod sh

# Run a command
docker exec client-site-enc-prod curl http://localhost/index.html

# View processes
docker exec client-site-enc-prod ps aux

# Check environment
docker exec client-site-enc-prod env
```

### Image Management
```bash
# List images
docker images

# View image size
docker images brooklyn99-heist:latest

# Remove image
docker rmi brooklyn99-heist:latest

# Prune unused images
docker image prune

# Save image to file
docker save brooklyn99-heist:latest > image.tar
```

---

## ⚙️ Configuration

### Change Port
```bash
# Edit .env
APP_PORT=8080

# Restart
docker-compose restart

# Access at http://localhost:8080
```

### Change Timezone
```bash
# Edit .env
TZ=Europe/Istanbul

# Restart
docker-compose restart
```

### Modify Nginx Config
```bash
# Edit nginx.conf
vi nginx.conf

# Rebuild
docker-compose build --no-cache

# Restart
docker-compose up -d
```

---

## 🔒 Security Checks

### View Security Headers
```bash
curl -I http://localhost/ | grep -i "X-"
```

### Check Running User
```bash
docker exec client-site-enc-prod ps aux | grep nginx
```

### View File Permissions
```bash
docker exec client-site-enc-prod ls -la /usr/share/nginx/html
```

---

## 📊 Common Issues & Solutions

### Port Already in Use
```bash
# Change APP_PORT in .env
APP_PORT=8080

# Or find what's using port 80
lsof -i :80
```

### Health Check Failing
```bash
# Check directly
docker exec client-site-enc-prod curl -f http://localhost/index.html

# View logs
docker logs client-site-enc-prod | grep -i error
```

### Out of Memory
```bash
# Increase memory limit in docker-compose.yml
# memory: 512M (from 256M)

# Rebuild and restart
docker-compose down
docker-compose build
docker-compose up -d
```

### Nginx Restart Loop
```bash
# Check nginx config
docker exec client-site-enc-prod nginx -t

# View error logs
docker exec client-site-enc-prod tail -f /var/log/nginx/error.log
```

---

## 📈 Performance Tuning

### Monitor Performance
```bash
# Real-time stats
docker stats client-site-enc-prod --no-stream

# Check compression
curl -I -H "Accept-Encoding: gzip" http://localhost/

# Measure response time
curl -w "Time: %{time_total}s\n" http://localhost/
```

### Optimize for Development
```bash
# Keep logs streaming
docker-compose logs -f app-prod

# Monitor in another terminal
docker stats client-site-enc-prod
```

---

## 🐳 Docker Info

### Image Details
```bash
# View full image config
docker inspect brooklyn99-heist:latest | jq '.Config'

# View layers
docker history brooklyn99-heist:latest

# View build date
docker inspect brooklyn99-heist:latest | grep -i created
```

### Network & Ports
```bash
# View port mappings
docker port client-site-enc-prod

# Check network
docker network ls
docker inspect misc-fe-enc_default
```

---

## 🚀 Deployment

### Push to Docker Hub
```bash
# Login
docker login

# Tag image
docker tag brooklyn99-heist:latest username/brooklyn99-heist:latest

# Push
docker push username/brooklyn99-heist:latest

# Pull later
docker pull username/brooklyn99-heist:latest
```

### Save & Load
```bash
# Save to file
docker save brooklyn99-heist:latest > brooklyn99.tar

# Load from file
docker load < brooklyn99.tar
```

---

## 🧹 Cleanup

### Remove All Containers
```bash
docker-compose down -v  # -v removes volumes too
```

### Prune System
```bash
# Remove unused containers
docker container prune

# Remove unused images
docker image prune

# Remove unused volumes
docker volume prune

# Remove all (CAREFUL!)
docker system prune -a
```

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| `DOCKER_BEST_PRACTICES.md` | Comprehensive guide (70+ KB) |
| `DOCKER_IMPROVEMENTS_SUMMARY.md` | Quick overview |
| `DOCKER_BEFORE_AND_AFTER.md` | Detailed comparison |
| `DOCKER_QUICK_REFERENCE.md` | This file |
| `Dockerfile` | Production build config |
| `nginx.conf` | Nginx server config |
| `docker-compose.yml` | Container orchestration |
| `.dockerignore` | Build context optimization |
| `.env.example` | Configuration template |

---

## 🔗 Useful Links

- [Docker Official Docs](https://docs.docker.com/)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)
- [Nginx Best Practices](https://nginx.org/en/docs/)
- [NIST Container Security](https://nvlpubs.nist.gov/nistpubs/SpecialPublications/NIST.SP.800-190.pdf)
- [CIS Docker Benchmark](https://www.cisecurity.org/benchmark/docker)

---

**Tip:** Save this file and bookmark for quick reference during development! 📌
