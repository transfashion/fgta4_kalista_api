#!/bin/bash

tail -F output/log.txt | awk '
/INFO/ {gsub("INFO", "\033[1;37mINFO\033[0m")} 
/ERROR/ {gsub("ERROR", "\033[1;31mERROR\033[0m")} 
{print}'