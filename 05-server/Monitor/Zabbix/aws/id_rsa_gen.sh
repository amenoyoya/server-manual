#!/bin/bash

rm -rf "$(dirname "$0")/id_rsa"
rm -rf "$(dirname "$0")/id_rsa.pub"

# interactive answer => [Empty password, Empty password]
echo "\n\n" | ssh-keygen -t rsa -b 4096 -f "$(dirname "$0")/id_rsa"