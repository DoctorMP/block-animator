#!/bin/sh
echo "Building the Block Animator WordPress plugin"
sass assets/scss/frontend.scss assets/css/frontend.css --no-source-map

dirname=${PWD##*/}
dirname=${dirname%%+(/)}
dirname=${dirname##*/}
cd ..

# The zip's top-level folder must be named "block-animator" regardless of what
# the local checkout is named (e.g. a GitHub archive download named
# "block-animator-main") — but skip the rename dance entirely when it's already
# correctly named, since `mv block-animator block-animator` errors ("cannot move
# to its own subdirectory") rather than being a silent no-op.
if [ "$dirname" != "block-animator" ]; then
	mv ./${dirname} ./block-animator
fi

mkdir ./block-animator/release
zip -r block-animator/release/block-animator.zip block-animator -x */\.* "block-animator/assets/scss/*" "block-animator/*.git*" "block-animator/*.log" "block-animator/release.sh" "block-animator/release/*" "block-animator/*.md" "block-animator/LICENSE"

if [ "$dirname" != "block-animator" ]; then
	mv ./block-animator ./${dirname}
fi

cd ${dirname}
echo "Done!"