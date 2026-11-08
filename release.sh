#!/bin/sh
echo "Building the Animora WordPress plugin"
sass assets/scss/frontend.scss assets/css/frontend.css --no-source-map

dirname=${PWD##*/}
dirname=${dirname%%+(/)}
dirname=${dirname##*/}
cd ..

# The zip's top-level folder must be named "animora" regardless of what
# the local checkout is named (e.g. a GitHub archive download named
# "animora-main") — but skip the rename dance entirely when it's already
# correctly named, since `mv animora animora` errors ("cannot move
# to its own subdirectory") rather than being a silent no-op.
if [ "$dirname" != "animora" ]; then
	mv ./${dirname} ./animora
fi

mkdir ./animora/release
zip -r animora/release/animora.zip animora -x */\.* "animora/assets/scss/*" "animora/*.git*" "animora/*.log" "animora/release.sh" "animora/release/*" "animora/*.md" "animora/LICENSE"

if [ "$dirname" != "animora" ]; then
	mv ./animora ./${dirname}
fi

cd ${dirname}
echo "Done!"