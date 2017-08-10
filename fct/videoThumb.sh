#!/bin/bash

# Script bash de conversion de vidéo en thumb gif animé pour le SaAM
# Polosson, le 28/03/2013

lastIFS="$IFS"
IFS=$'\n'

path=`dirname $1`
fileName=`basename $1`
sec=$(echo "scale=1; $2/1000.0" | bc)
fps=$(echo "scale=4; 1/$2*1000" | bc)
cents=$(echo "scale=0; $2/10" | bc)

echo "conversion de $fileName :"
echo "    vitesse : $2 ms. (soit $cents centiemes, ou $fps fps.)"
echo "    largeur : $3"
echo "    hauteur : $4"

cd $path
mkdir -p thumbs/tmp
avconv -i $1 -vsync 1 -r $fps -an -s $3x$4 -f image2 -y thumbs/tmp/$fileName-tmp_%d.png
cd thumbs/tmp/
mogrify -format gif *.png
gifsicle --colors=256 --delay=$cents --loopcount=0 --dither -O3 *.gif > $fileName-anim.gif
mv $fileName-anim.gif ../vthumb_$fileName.gif
cd ../
rm -R tmp/
IFS="$lastIFS"