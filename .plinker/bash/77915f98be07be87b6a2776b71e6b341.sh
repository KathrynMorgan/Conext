#!/bin/bash

if /usr/sbin/service --status-all | grep -Fq 'nginx'; then
  nginx -s reload
fi