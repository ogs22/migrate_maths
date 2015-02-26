#/bin/bash

cd /usr/local/www/drupal/sites/www.maths.cam.ac.uk/files/pre2014/

echo "<body><h1>DELETE ME</h1></body>" > import-index

find . -type d -exec ln import-index "{}/index.html" \;

rm import-index

find . -name "*~" -exec rm "{}" \;

find . -name "*html-*" -exec rm "{}" \;

rm undergrad/i.html

rm undergrad/studentsurvey/index.html
rm undergrad/studyskills/text/index.html

