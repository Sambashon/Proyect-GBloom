#!/bin/bash

rm -f certs/etc/*

rm -f certs/var/*

docker compose -f docker-compose.yaml run --rm certbot