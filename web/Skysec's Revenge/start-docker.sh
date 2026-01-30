#!/bin/bash
set -e

IMAGE_NAME="skysec-revenge"
CONTAINER_NAME="skysec-revenge"
PORT=80

echo "==================================="
echo "  Skysec's Revenge - CTF Setup"
echo "==================================="
echo ""

# Build
echo "🔨 Building Docker image..."
docker build -t $IMAGE_NAME .

# Remove old container
echo "🧹 Removing old container..."
docker container rm -f $CONTAINER_NAME 2>/dev/null || true

# Run
echo "🚀 Starting container..."
docker run -d \
  --name $CONTAINER_NAME \
  -p $PORT:80 \
  --restart unless-stopped \
  $IMAGE_NAME

# Check
if docker ps | grep -q $CONTAINER_NAME; then
  echo ""
  echo "✅ Success! Challenge running at: http://localhost:$PORT"
  echo ""
  echo "Commands:"
  echo "  Logs:    docker logs -f $CONTAINER_NAME"
  echo "  Stop:    docker stop $CONTAINER_NAME"
  echo "  Restart: docker restart $CONTAINER_NAME"
else
  echo "❌ Failed to start! Check: docker logs $CONTAINER_NAME"
  exit 1
fi

