#!/bin/bash 
# export PS1='\[\033[32m\]\u@\h\[\033[0m\]:\[\033[34m\]\W\[\033[0m\]\$ '
# export PS1='\[\033[34m\]\W\[\033[0m\]\$ '


URL="http://172.18.20.249:8132/public/api"

endpoint="$URL/Transfashion/KalistaApi/Session/RegisterExternalSession";

echo POST $endpoint
curl -X POST \
     -D - \
	 -H "Content-Type: application/json" \
	 -H "App-Id: transfashionid" \
	 -H "App-Secret: n3k4n2fdmf3fse" \
     -d '{"request":{"sessid": "1234"}}' \
	 $endpoint

