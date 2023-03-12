#!/bin/bash

# Check if .develop file exists
if [ -f .develop ]; then
    # Copy env.develop to .env in backend folder
    cp backend/.env.develop backend/.env
elif [ -f .test ]; then
    # Copy backend/.env.test to .env in backend folder
    cp backend/.env.test backend/.env
elif [ -f .stag ]; then
    # Copy backend/.env.stag to .env in backend folder
    cp backend/.env.stag backend/.env
elif [ -f .production ]; then
    # Copy backend/.env.production to .env in backend folder
    cp env.production backend/.env
else
    echo "No environment file found."
fi