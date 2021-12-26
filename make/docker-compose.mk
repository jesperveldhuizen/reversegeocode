.PHONY: up
up:
	cd qlico && docker-compose up -d

.PHONY: down
down:
	cd qlico && docker-compose down

.PHONY: restart
restart:
	cd qlico && docker-compose restart

.PHONY: shell
shell:
	cd qlico && docker-compose exec php sh
