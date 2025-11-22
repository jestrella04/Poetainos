#!/bin/bash

# Set working directory
cd public/images

# Use Inkscape to generate PNGs from the SVG logos
inkscape --export-background-opacity=0 --export-width=32 --export-type=png --export-filename=logo-32.png logo.svg
inkscape --export-background-opacity=0 --export-width=64 --export-type=png --export-filename=logo-64.png logo.svg
inkscape --export-background-opacity=0 --export-width=128 --export-type=png --export-filename=logo-128.png logo.svg
inkscape --export-background-opacity=0 --export-width=144 --export-type=png --export-filename=logo-144.png logo.svg
inkscape --export-background-opacity=0 --export-width=192 --export-type=png --export-filename=logo-192.png logo.svg
inkscape --export-background-opacity=0 --export-width=196 --export-type=png --export-filename=logo-196.png logo.svg
inkscape --export-background-opacity=0 --export-width=256 --export-type=png --export-filename=logo-256.png logo.svg
inkscape --export-background-opacity=0 --export-width=512 --export-type=png --export-filename=logo-512.png logo.svg
inkscape --export-background-opacity=0 --export-width=512 --export-type=png --export-filename=logo-maskable.png logo-maskable.svg
inkscape --export-background-opacity=0 --export-width=512 --export-type=png --export-filename=logo-mono.png logo-mono.svg
