#!/bin/bash
docker-compose exec mysql /docker-entrypoint-initdb.d/install.sh
