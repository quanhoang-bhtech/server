<!--
	- @copyright 2022 Christopher Ng <chrng8@gmail.com>
	-
	- @author Christopher Ng <chrng8@gmail.com>
	-
	- @license AGPL-3.0-or-later
	-
	- This program is free software: you can redistribute it and/or modify
	- it under the terms of the GNU Affero General Public License as
	- published by the Free Software Foundation, either version 3 of the
	- License, or (at your option) any later version.
	-
	- This program is distributed in the hope that it will be useful,
	- but WITHOUT ANY WARRANTY; without even the implied warranty of
	- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	- GNU Affero General Public License for more details.
	-
	- You should have received a copy of the GNU Affero General Public License
	- along with this program. If not, see <http://www.gnu.org/licenses/>.
	-
-->

<template>
	<section>
		<HeaderBar v-bind.sync="property"
			:is-editable="isEditable" />

		<AccountPropertyInput v-bind.sync="property"
			:placeholder="placeholder"
			:is-editable="isEditable"
			:multi-line="multiLine"
			:empty-string-valid="emptyStringValid"
			:on-save="onSave" />
	</section>
</template>

<script>
import AccountPropertyInput from './AccountPropertyInput.vue'
import HeaderBar from '../shared/HeaderBar.vue'

export default {
	name: 'AccountPropertySection',

	components: {
		AccountPropertyInput,
		HeaderBar,
	},

	props: {
		property: {
			type: Object,
			required: true,
		},
		placeholder: {
			type: String,
			required: true,
		},
		isEditable: {
			type: Boolean,
			default: true,
		},
		multiLine: {
			type: Boolean,
			default: false,
		},
		emptyStringValid: {
			type: Boolean,
			default: true,
		},
		onSave: {
			type: Function,
			default: null,
		},
	},
}
</script>

<style lang="scss" scoped>
section {
	padding: 10px 10px;

	&::v-deep button:disabled {
		cursor: default;
	}
}
</style>
