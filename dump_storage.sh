#!/bin/bash

if [ $# -ne 2 ]
    then
        echo "Incorrect number of arguments passed."
        exit
fi

if [ -z "$1" ]
    then
        echo "Incorrect value of first argument passed."
        exit
fi

if [ -z "$2" ]
    then
        echo "Incorrect value of second argument passed."
        exit
fi


TMP_WORKING_DIR=$1
DIRECTORY_PATH=$2

ARCHIVE_EXTENSIONS="*.bz2 *.gz"
BACKUP_DIRECTORY="backup"
README_FILE="README.txt"

ARCHIVE_PATH=${DIRECTORY_PATH}/web/dumps/ezplatform_page_storage.tar.bz2
SOURCE_PATH=${DIRECTORY_PATH}/web/var/site/storage/

PACKAGES_PATH="original/application"
ALIASES_PATH="images/_aliases"

mkdir ${TMP_WORKING_DIR}

echo "Copying files."
rsync -a ${SOURCE_PATH} ${TMP_WORKING_DIR} --exclude=${ALIASES_PATH} --exclude=${PACKAGES_PATH}

mkdir ${TMP_WORKING_DIR}/${PACKAGES_PATH}

cd ${SOURCE_PATH}/${PACKAGES_PATH}
echo "Creating text file."
echo "Please download archive from http://ezplatform.com" > ${README_FILE}

echo "Creating archives."
for archive in ${ARCHIVE_EXTENSIONS}
do
    tar -cjf "${TMP_WORKING_DIR}/${PACKAGES_PATH}/${archive}" ${README_FILE}
done

echo "Creating dump."
tar --exclude=${BACKUP_DIRECTORY} --exclude=${README_FILE} -cjf ${ARCHIVE_PATH} -C ${TMP_WORKING_DIR} .

echo "Cleaning."
rm -R ${README_FILE}
rm -R ${TMP_WORKING_DIR}
echo "Done."
