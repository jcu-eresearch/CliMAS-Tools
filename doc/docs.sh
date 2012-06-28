
#phpdoc run -d /www/eresearch/TDH-Tools/Search -t /www/eresearch/TDH-Tools/doc/Search --defaultpackagename AP02
#phpdoc run -d /www/eresearch/TDH-Tools/Utilities -t /www/eresearch/TDH-Tools/doc/Utilities --defaultpackagename AP02

if [ "$1" = "git" ]
then
    echo "Commiting updated documents to git-hub"
    git commit -m "documentation updated using phpDocumentor"
    git push
fi

