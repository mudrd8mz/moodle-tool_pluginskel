all : skeltests

skeltests :
	mkdir ~/tmp/skeltests
	for file in *.yaml; do mkdir ~/tmp/skeltests/$$(basename $${file} .yaml); php ../../cli/generate.php --target-dir=~/tmp/skeltests/$$(basename $${file} .yaml) $$(realpath $${file}); done

.PHONY: all skeltests
