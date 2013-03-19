
# --- make dirs for metadata files to be copied to ------
pushd /home/TDH/apps/CliMAS/
#
rm -rf metadata_suitability_prev
rm -rf metadata_biodiversity_prev
rm -rf metadata_reports_prev
#
mv metadata_suitability metadata_suitability_prev
mv metadata_biodiversity metadata_biodiversity_prev
mv metadata_reports metadata_reports_prev
#
mkdir metadata_suitability
mkdir metadata_biodiversity
mkdir metadata_reports
#
popd

# --- copy suitability data into place ------
pushd /home/TDH/data/CliMAS
#
rsync -hav --filter '+ *_*/' \
           --filter '+ *_*/climas-suitability-metadata-override.json' \
           --filter '- *' \
           species/ /home/TDH/apps/CliMAS/metadata_suitability
#
popd

# --- copy biodiversity data into place ------
pushd /home/TDH/data/CliMAS
mkdir /home/TDH/apps/CliMAS/metadata_biodiversity/vertebrates
cp biodiversity/climas-biodiversity-metadata-override.json /home/TDH/apps/CliMAS/metadata_biodiversity/vertebrates/
cd ByClass
find . -maxdepth 1 -mindepth 1 -print0 | xargs -0 -I TAXADIR sh -c '{ mkdir /home/TDH/apps/CliMAS/metadata_biodiversity/TAXADIR; cp TAXADIR/biodiversity/climas-biodiversity-metadata-override.json /home/TDH/apps/CliMAS/metadata_biodiversity/TAXADIR/; }'
cd ..
cd ByFamily
find . -maxdepth 1 -mindepth 1 -print0 | xargs -0 -I TAXADIR sh -c '{ mkdir /home/TDH/apps/CliMAS/metadata_biodiversity/TAXADIR; cp TAXADIR/biodiversity/climas-biodiversity-metadata-override.json /home/TDH/apps/CliMAS/metadata_biodiversity/TAXADIR/; }'
cd ..
cd ByGenus
find . -maxdepth 1 -mindepth 1 -print0 | xargs -0 -I TAXADIR sh -c '{ mkdir /home/TDH/apps/CliMAS/metadata_biodiversity/TAXADIR; cp TAXADIR/biodiversity/climas-biodiversity-metadata-override.json /home/TDH/apps/CliMAS/metadata_biodiversity/TAXADIR/; }'
cd ..
#
popd

# --- copy report data into place ------
pushd /home/TDH/data/CliMAS
#
rsync -hav --filter '+ */' \
           --filter '+ */climas-reports-metadata-override.json' \
           --filter '- *' \
           reports/regions/ /home/TDH/apps/CliMAS/metadata_reports
#
popd

