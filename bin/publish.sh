#! /bin/bash
set -e

# A modification of Dean Clatworthy's deploy script as found here: https://github.com/deanc/wordpress-plugin-git-svn
# The difference is that this script lives in the plugin's git repo & doesn't require an existing SVN repo.

DRY_RUN=${DRY_RUN:-"false"}
echo "Dry run: $DRY_RUN"

# main config
PLUGINSLUG="rownd-accounts-and-authentication"
CURRENTDIR=`pwd`
MAINFILE="index.php" # this should be the name of your main php file in the wordpress plugin

# git config
GITPATH="$CURRENTDIR/" # this file should be in the base of your git repository

# svn config
SVNPATH="$(mktemp -d)/$PLUGINSLUG" # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="https://plugins.svn.wordpress.org/rownd-accounts-and-authentication/" # Remote SVN repo on wordpress.org, with no trailing slash
# SVNUSER="______your-wp-username______" # your svn username

if [ -z "$SVNUSER" ]; then
	echo "SVNUSER is not set. Unable to proceed."
	exit 1
fi


# Let's begin...
echo ".........................................."
echo
echo "Preparing to deploy wordpress plugin"
echo
echo ".........................................."
echo

# Check version in readme.txt is the same as plugin file
NEWVERSION1=`awk '/Stable tag:/ { print $NF }' $GITPATH/readme.txt | tr -d '\r'`
echo "readme version: $NEWVERSION1"
NEWVERSION2=`awk '/\* Version:/ { print $NF }' $GITPATH/$MAINFILE  | tr -d '\r'`
echo "$MAINFILE version: $NEWVERSION2"
NEWVERSION3=`awk -F"'" '/define.*ROWND_PLUGIN_VERSION/ && NF>=4 { gsub(/[[:space:]]+/, "", $4); if ($4 != "") print $4 }' $GITPATH/$MAINFILE | tr -d '\r'/`
echo "$MAINFILE programmatic version: $NEWVERSION3"

if [ "$NEWVERSION1" != "$NEWVERSION2" ] || [ "$NEWVERSION2" != "$NEWVERSION3" ]; then echo "Versions don't match. Exiting...."; exit 1; fi

echo "Versions match in readme.txt and $MAINFILE file. Let's proceed..."

COMMITMSG=`git log --pretty=%B | head -n 1`

echo
echo "Creating local copy of SVN repo ..."
svn co -q $SVNURL $SVNPATH

echo "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

echo "Ignoring undesired plugin files"
svn propset svn:ignore "deploy.sh
README.md
bin
tests
assets
.release-it.json
commitlint.config.js
VERSION
.distignore
$(cat .distignore)
.vscode
.git
.gitignore" "$SVNPATH/trunk/"

echo "Moving assets"
mkdir -p $SVNPATH/assets/
mv $SVNPATH/trunk/assets/* $SVNPATH/assets/
svn add -q --force $SVNPATH/assets/

echo "Changing directory to SVN"
cd $SVNPATH/trunk/

# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add
echo "committing to trunk"

if [ $DRY_RUN == 'false' ]; then
	echo "SVN commit"
	# svn commit --username=$SVNUSER -m "$COMMITMSG" --force-interactive
fi

# Update WP assets folders
echo "Updating WP plugin repo assets & committing"
cd $SVNPATH/assets/
if [ $DRY_RUN == 'false' ]; then
	echo "SVN commit"
	# svn commit --username=$SVNUSER -m "Updating plugin assets" --force-interactive
fi

# Tag the release
echo "Creating new SVN tag & committing it"
echo "Tag name: $NEWVERSION1"
cd $SVNPATH
svn copy trunk/ tags/$NEWVERSION1/
cd $SVNPATH/tags/$NEWVERSION1

if [ $DRY_RUN == 'false' ]; then
	echo "SVN commit"
	# svn commit --username=$SVNUSER -m "Tagging version $NEWVERSION1" --force-interactive
fi

echo "Removing temporary directory $SVNPATH"

if [ $DRY_RUN == 'false' ]; then
	rm -fr $SVNPATH/
fi

echo "*** DONE ***"
