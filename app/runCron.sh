#!/bin/bash

for supplier in trige ewe uspon irismega dsc roaming gembird
do
  bash runSync.sh $supplier &
done