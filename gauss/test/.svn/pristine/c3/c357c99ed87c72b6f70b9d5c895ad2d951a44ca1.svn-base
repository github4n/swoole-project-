imgtag = gambling/php:latest

all: iid

iid: Dockerfile entrypoint.sh
	if [ -f "iid" ]; then $(MAKE) clean; fi
	docker build --rm -t $(imgtag) --iidfile iid .

.PHONY: clean
clean:
	docker rmi $(imgtag)
	rm iid
