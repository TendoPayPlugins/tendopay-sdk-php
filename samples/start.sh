#!/usr/bin/env bash

DOC_ROOT=$(dirname $(realpath $0))
php -S localhost:8000 -t $DOC_ROOT
