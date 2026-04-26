#!/bin/bash

SRC=(
	"https://raw.githubusercontent.com/disposable-email-domains/disposable-email-domains/refs/heads/main/disposable_email_blocklist.conf"
	"https://raw.githubusercontent.com/FGRibreau/mailchecker/refs/heads/master/list.txt"
)

OUTPUT_FILE="src/disposable_email_blocklist.conf"
if [ ! -f src/disposable_email_blocklist.conf ]; then
	echo "run at repository top level";
	exit 1;
fi

echo "" > $OUTPUT_FILE
for src in ${SRC[@]}; do
    curl -s $src -o $OUTPUT_FILE.tmp
	echo "--- $src ---";
	wc -l $OUTPUT_FILE.tmp;
	cat $OUTPUT_FILE.tmp >> $OUTPUT_FILE
	rm $OUTPUT_FILE.tmp
done

cat src/disposable_email_blocklist.conf | sort | uniq > src/disposable_email_blocklist.conf.tmp
mv src/disposable_email_blocklist.conf.tmp src/disposable_email_blocklist.conf

echo "--- Total in $OUTPUT_FILE ---";
wc -l src/disposable_email_blocklist.conf