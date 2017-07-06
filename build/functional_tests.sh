#!/bin/bash

cd x2engine/protected/tests

echo "=============== Running functional tests for: $FUNC_TEST ==============="
echo "phpunit --configuration phpunit_functional.xml functional/$FUNC_TEST | tee /tmp/report-$FUNC_TEST &"
phpunit --configuration phpunit_functional.xml functional/$FUNC_TEST | tee /tmp/report-$FUNC_TEST
echo "=============== Done with functional tests! ==============="

cd ../../..
