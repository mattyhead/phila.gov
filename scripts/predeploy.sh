#!/bin/bash

/usr/local/bin/aws s3 cp s3://phila-prod-environment/environment /home/ubuntu/.ssh/environment

sudo DEBIAN_FRONTEND=noninteractive

sudo apt-get update
apt-get -yq upgrade
