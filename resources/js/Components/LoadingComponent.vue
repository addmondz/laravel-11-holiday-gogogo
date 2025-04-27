<template>
	<div class="breeding-rhombus-spinner m-auto">
		<div class="rhombus child-1"></div>
		<div class="rhombus child-2"></div>
		<div class="rhombus child-3"></div>
		<div class="rhombus child-4"></div>
		<div class="rhombus child-5"></div>
		<div class="rhombus child-6"></div>
		<div class="rhombus child-7"></div>
		<div class="rhombus child-8"></div>
		<div class="rhombus big"></div>
	</div>
</template>

<style>
:root {
	--spinner-size: 65px;
	/* Default size, customizable */
	/* --spinner-color: black; */
	--spinner-color: rgb(79 70 229 / var(--tw-bg-opacity, 1));
	/* Default color */
}

.breeding-rhombus-spinner {
	height: var(--spinner-size);
	width: var(--spinner-size);
	position: relative;
	transform: rotate(45deg);
}

.breeding-rhombus-spinner,
.breeding-rhombus-spinner * {
	box-sizing: border-box;
}

.breeding-rhombus-spinner .rhombus {
	height: calc(var(--spinner-size) / 7.5);
	width: calc(var(--spinner-size) / 7.5);
	animation-duration: 2s;
	top: calc(var(--spinner-size) / 2.3077);
	left: calc(var(--spinner-size) / 2.3077);
	background-color: var(--spinner-color);
	position: absolute;
	animation-iteration-count: infinite;
}

.breeding-rhombus-spinner .rhombus.child-1 {
	animation-name: rhombus-animation-1;
	animation-delay: calc(100ms * 1);
}

.breeding-rhombus-spinner .rhombus.child-2 {
	animation-name: rhombus-animation-2;
	animation-delay: calc(100ms * 2);
}

.breeding-rhombus-spinner .rhombus.child-3 {
	animation-name: rhombus-animation-3;
	animation-delay: calc(100ms * 3);
}

.breeding-rhombus-spinner .rhombus.child-4 {
	animation-name: rhombus-animation-4;
	animation-delay: calc(100ms * 4);
}

.breeding-rhombus-spinner .rhombus.child-5 {
	animation-name: rhombus-animation-5;
	animation-delay: calc(100ms * 5);
}

.breeding-rhombus-spinner .rhombus.child-6 {
	animation-name: rhombus-animation-6;
	animation-delay: calc(100ms * 6);
}

.breeding-rhombus-spinner .rhombus.child-7 {
	animation-name: rhombus-animation-7;
	animation-delay: calc(100ms * 7);
}

.breeding-rhombus-spinner .rhombus.child-8 {
	animation-name: rhombus-animation-8;
	animation-delay: calc(100ms * 8);
}

.breeding-rhombus-spinner .rhombus.big {
	height: calc(var(--spinner-size) / 3);
	width: calc(var(--spinner-size) / 3);
	top: calc(var(--spinner-size) / 3);
	left: calc(var(--spinner-size) / 3);
	background-color: var(--spinner-color);
	animation: rhombus-animation-big 2s infinite;
	animation-delay: 0.5s;
}

@keyframes rhombus-animation-1 {
	50% {
		transform: translate(-325%, -325%);
	}
}

@keyframes rhombus-animation-2 {
	50% {
		transform: translate(0, -325%);
	}
}

@keyframes rhombus-animation-3 {
	50% {
		transform: translate(325%, -325%);
	}
}

@keyframes rhombus-animation-4 {
	50% {
		transform: translate(325%, 0);
	}
}

@keyframes rhombus-animation-5 {
	50% {
		transform: translate(325%, 325%);
	}
}

@keyframes rhombus-animation-6 {
	50% {
		transform: translate(0, 325%);
	}
}

@keyframes rhombus-animation-7 {
	50% {
		transform: translate(-325%, 325%);
	}
}

@keyframes rhombus-animation-8 {
	50% {
		transform: translate(-325%, 0);
	}
}

@keyframes rhombus-animation-big {
	50% {
		transform: scale(0.5);
	}
}
</style>
