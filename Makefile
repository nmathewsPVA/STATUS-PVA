.PHONY: clean default docker

default: docker

docker:
	docker compose build --pull
	docker compose up -d

clean:
	if [ "$$(docker compose ps | wc -l)" -gt "0" ]; then docker compose down -v; docker compose rm; fi
