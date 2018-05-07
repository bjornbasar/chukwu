mkdir -p application/views/.smarty/.compiled
mkdir -p application/views/.smarty/.cache
mkdir -p log
mkdir -p uploads
mkdir -p data/auth
mkdir -p data/db
mkdir -p data/cache
mkdir -p data/search
mkdir -p data/sql
chmod -R 0777 application/views/.smarty/.compiled
chmod -R 0777 application/views/.smarty/.cache
chmod -R 0777 log
chmod -R 0777 uploads
chmod -R 0777 data/auth
chmod -R 0777 data/db
chmod -R 0777 data/cache
chmod -R 0777 data/search
chmod -R 0777 data/sql
chmod -R 0777 data/sqlite
rm -fr application/views/.smarty/.compiled/*
rm -fr application/views/.smarty/.cache/*
svn propset svn:ignore -F .ignores .
svn propset svn:ignore -F conf/.ignores conf/.
svn propset svn:ignore -F includes/.ignores includes/.
svn propset svn:ignore -F application/views/.ignores application/views/.
