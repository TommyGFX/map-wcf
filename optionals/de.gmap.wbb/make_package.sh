#!/bin/bash
#
# packages filesystem as wcf package
# parameter = version (e.g. 1.2.0)
#
# by Torben Brodt

TITLE="de.gmap.wbb"
VERSION="1.0.0"

# output version
if [ "$1" = "-v" ]; then
	echo "$VERSION"
	exit
fi

# check param
if [ "$1" ]; then
	VERSION=$1
fi

# welcome output
echo ""
echo "$TITLE wird erstellt............................"
echo ""

# assign vars
BUILDDATE=`date +"%Y-%m-%d"`

# create files.tar
cd files
tar cvf ../files.tar * --exclude=*/.svn*
cd ..

# create templates.tar
if [ -d "templates" ]; then
	cd templates
	tar cvf ../templates.tar * --exclude=*/.svn*
	cd ..
fi

# create acptemplates.tar
if [ -d "acptemplates" ]; then
	cd acptemplates
	tar cvf ../acptemplates.tar * --exclude=*/.svn*
	cd ..
fi

# package requirements
if [ -d "requirements" ]; then
	TAR_STRING="$TAR_STRING optionals/*.tar.gz"
	cd requirements
	dirs=`find . -mindepth 1 -maxdepth 1 -type d | grep -v .svn`

	for i in $dirs
	do
		cd $i
		PACKVERSION=`./make_package.sh -v`
		./make_package.sh $PACKVERSION
		mv ${i}_${PACKVERSION}.tar.gz ..
		cd ..
	done

	cd ..
fi

# package optionals
if [ -d "optionals" ]; then
	TAR_STRING="$TAR_STRING optionals/*.tar.gz"
	cd optionals
	dirs=`find . -mindepth 1 -maxdepth 1 -type d | grep -v .svn`

	for i in $dirs
	do
		cd $i
		PACKVERSION=`./make_package.sh -v`
		./make_package.sh $PACKVERSION
		mv ${i}_${PACKVERSION}.tar.gz ..
		cd ..
	done

	cd ..
fi

# rename files for temporary operations
mv package.xml package.tmp

# replacements in package.xml
sed "s/VERSION/$VERSION/" package.tmp > package.tmp2
sed "s/DATE/$BUILDDATE/" package.tmp2 > package.xml
rm package.tmp2

# remove old package
if [ -f "${TITLE}_$VERSION.tar" ] ; then
	rm ${TITLE}_$VERSION.tar.gz
fi

# create new package
VARX=`find *.diff 2>/dev/null`
if [ "$VARX" ]; then
	TAR_STRING="$TAR_STRING *.diff"
fi
VARX=`find *.sql 2>/dev/null`
if [ "$VARX" ]; then
	TAR_STRING="$TAR_STRING *.sql"
fi
tar cfz ${TITLE}_$VERSION.tar.gz *.xml *.tar $TAR_STRING

# rename back
mv package.tmp package.xml

# remove tmp files
rm files.tar
if [ -f "templates.tar" ]; then
	rm templates.tar
fi
if [ -f "acptemplates.tar" ]; then
	rm acptemplates.tar
fi
if [ -d "optionals" ]; then
	rm optionals/*.tar.gz
fi
if [ -d "requirements" ]; then
	rm requirements/*.tar.gz
fi
