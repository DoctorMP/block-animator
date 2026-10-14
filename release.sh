#!/bin/sh
echo "Building the GutenBoing WordPress plugin"
sass assets/scss/frontend.scss assets/css/frontend.css --no-source-map

dirname=${PWD##*/}
dirname=${dirname%%+(/)}
dirname=${dirname##*/}
cd ..

# The zip's top-level folder must be named "gutenboing" regardless of what
# the local checkout is named (e.g. a GitHub archive download named
# "gutenboing-main") — but skip the rename dance entirely when it's already
# correctly named, since `mv gutenboing gutenboing` errors ("cannot move
# to its own subdirectory") rather than being a silent no-op.
if [ "$dirname" != "gutenboing" ]; then
	mv ./${dirname} ./gutenboing
fi

mkdir ./gutenboing/release
zip -r gutenboing/release/gutenboing.zip gutenboing -x */\.* "gutenboing/assets/scss/*" "gutenboing/*.git*" "gutenboing/*.log" "gutenboing/release.sh" "gutenboing/release/*" "gutenboing/*.md" "gutenboing/LICENSE"

if [ "$dirname" != "gutenboing" ]; then
	mv ./gutenboing ./${dirname}
fi

cd ${dirname}
echo "Done!"