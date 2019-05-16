modules := Plat Site User
protocols := Websocket Http Task
daemons := $(foreach module,$(modules),$(foreach protocol,$(protocols),daemon.$(module).$(protocol).php))

all: $(daemons)

$(daemons): daemon.php
	sed -e "s/MODULE/$(word 2,$(subst ., ,$(@)))/g" \
	    -e 's/PROTOCOL/$(word 3,$(subst ., ,$(@)))/g' \
		daemon.php > $@

clean:
	rm $(daemons)