#!/bin/bash

for action in parse create update
do
     for supplier in ewe uspon
         do
             php cli.php $action $supplier
         done
done

