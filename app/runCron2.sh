#!/bin/bash

for supplier in kimtec comtrade asbis telitPower beli atom
do
  bash runSync.sh $supplier &
done