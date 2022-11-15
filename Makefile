all: dev-setup build-js-production

# Dev env management
dev-setup: clean npm-init

npm-init:
	npm i

npm-update:
	npm update

# Building
build-js:
	npm run dev

build-js-production:
	npm run build

watch-js:
	npm run watch

# Linting
lint-fix:
	npm run lint:fix

lint-fix-watch:
	npm run lint:fix-watch

# Cleaning
clean:

clean-git: clean
	git checkout -- apps/accessibility/js/
	git checkout -- apps/comments/js/
	git checkout -- apps/dashboard/js/
	git checkout -- apps/dav/js/
	git checkout -- apps/files/js/dist/
	git checkout -- apps/files_sharing/js/dist/
	git checkout -- apps/files_trashbin/js/
	git checkout -- apps/files_versions/js/
	git checkout -- apps/oauth2/js/
	git checkout -- apps/settings/js/vue-*
	git checkout -- apps/systemtags/js/systemtags.*
	git checkout -- apps/twofactor_backupcodes/js
	git checkout -- apps/updatenotification/js/updatenotification.*
	git checkout -- apps/user_status/js/
	git checkout -- apps/weather_status/js/
	git checkout -- apps/workflowengine/js/
	git checkout -- core/js/dist
