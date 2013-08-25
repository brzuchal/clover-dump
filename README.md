# clover-dump

Clover Dump is a simple application that parses through a clover.xml file and spits out the coverage results in percentages to the screen. 

## Installation

Include `clover-dump` in your project by adding it to your `composer.json` file:

    {
        "require": {
            "clover/dump": "1.*"
        }
    }

## Usage

To simply dump out the results, run `vendor/bin/clover-dump clover.xml`:

    $ bin/clover-dump clover.xml 
    Clover Code Coverage Report:
    
     - 100.00% PhpGedcom\Gedcom
     -   0.00% PhpGedcom\Record\Addr
     -  50.00% PhpGedcom\Record\Caln
     -   0.00% PhpGedcom\Record\Chan
     -  37.93% PhpGedcom\Record\Fam
     -  95.83% PhpGedcom\Record\Head
     -  38.26% PhpGedcom\Record\Indi
    
    Code Coverage: 55.19%

If you only want summary information (instead of every file), add the `--summary-only` flag: 

    $ bin/clover-dump --summary-only clover.xml 
    Clover Code Coverage Report:
    
    Code Coverage: 55.19%

You can change which percentages toggle the warning and error colors on the console: 

    $ bin/clover-dump --warning-percentage=70 --error-percentage=50 clover.xml 

You can also make the application return an error return value at a certain percantage (which could be used by a CI server to mark a build as failed): 

    $ bin/clover-dump --fail-at 40 
    
