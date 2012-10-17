#!/bin/tcsh

foreach number (`seq $1 $2`)
  echo "Stop QSUB JOB - $number"
  qdel $number
end
