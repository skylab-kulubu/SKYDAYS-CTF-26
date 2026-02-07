docker build -t pwn .
docker run --privileged -p 1337:1337 -it pwn

on a seperate terminal:
nc localhost 1337
