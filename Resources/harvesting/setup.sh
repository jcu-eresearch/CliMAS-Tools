echo "Setting up redbox for test harvesting... continue? [Enter]"
read

HERE='/home/daniel/projects/ap02/TDH-Tools/Resources/harvesting'
DEPLOY='/home/daniel/projects/redbox/TDH-Research-Data-Catalogue.git/target/deploy'

echo ""
echo "*** copying files in $HERE/harvest to $DEPLOY/home/harvest .."
cp -r $HERE/harvest/* $DEPLOY/home/harvest/

echo "*** copying cleardown script to $DEPLOY/server .."
cp -r $HERE/cleardownredbox.sh $DEPLOY/server/

echo "*** running cleardown script .."

pushd $DEPLOY/server/
./tf.sh stop
./cleardownredbox.sh
popd

echo ""
echo "*** copying $HERE/climas/*.default.json into $DEPLOY/home/data/CliMAS and ./test .."

rm -rf $DEPLOY/home/data/CliMAS
mkdir $DEPLOY/home/data/CliMAS

mkdir $DEPLOY/home/data/CliMAS/reports
cp $HERE/climas/climas-reports-metadata-default.json $DEPLOY/home/data/CliMAS/reports/
cp -r $HERE/climas/metadata_reports/* $DEPLOY/home/data/CliMAS/reports/

mkdir $DEPLOY/home/data/CliMAS/biodiversity
cp $HERE/climas/climas-biodiversity-metadata-default.json $DEPLOY/home/data/CliMAS/biodiversity/
cp -r $HERE/climas/metadata_biodiversity/* $DEPLOY/home/data/CliMAS/biodiversity/

mkdir $DEPLOY/home/data/CliMAS/suitability
cp $HERE/climas/climas-suitability-metadata-default.json $DEPLOY/home/data/CliMAS/suitability/
cp -r $HERE/climas/metadata_suitability/* $DEPLOY/home/data/CliMAS/suitability/


echo ""
echo "*** harvesting: first start the server.."

pushd $DEPLOY/server/
./tf.sh start
echo ""
echo "*** harvesting: next, wait 60 seconds for the smoke to clear.."
sleep 5s
echo "***                        55 seconds to go, be patient"
sleep 25s
echo "***                        30 seconds to go.."
sleep 25s
echo "***                         5 seconds left!"
sleep 5s
echo ""
echo "*** harvesting: finally, actually harvest.."

./tf_harvest.sh directoryHarvest-climas-suitability
./tf_harvest.sh directoryHarvest-climas-biodiversity
./tf_harvest.sh directoryHarvest-climas-reports
popd

echo ""
echo "*** "
echo "*** Done."
echo "*** "