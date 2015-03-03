#/bin/bash                                                                                                                                                             

cd /usr/local/www/drupal/sites/www.maths.cam.ac.uk/files/pre2014/

echo "<body><h1>DELETE ME</h1></body>" > import-index

find . -type d -exec ln import-index "{}/index.html" \;

rm import-index

find . -name "*~" -exec rm "{}" \;

find . -name "*html-*" -exec rm "{}" \;

rm index*

rm -rf postgrad/cca-old
rm -rf postgrad/cca-tempmove
rm -rf postgrad/cca-testing

rm -rf postgrad/index2.html

rm computing/docs
