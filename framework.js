let tree = {};
let secretURL = null;
let expanded = true;
let animating = false;
const subscribed = [];

/**
 * Add a callback that is called every time the tree changes
 */
export function subscribe( callback ) {
	subscribed.push( callback );
}

/**
 * Call this every time the tree changes, so that it triggers
 * any UI changes needed
 */
export function updateTree( newTree = null ) {
	const oldTree = tree;
	if ( newTree !== null ) {
		tree = newTree;
	}
	subscribed.forEach( callback => callback( tree, secretURL, expanded ) );
}

export function updateSecretURL( url ) {
	secretURL = url;
	subscribed.forEach( callback => callback( tree, secretURL, expanded ) );
}

export function updateExpanded( newExpanded ) {
	expanded = newExpanded;
	subscribed.forEach( callback => callback( tree, secretURL, expanded ) );
}

export function getTree() {
	return tree;
}

export function getSecretURL() {
	return secretURL;
}

export function getExpanded() {
	return expanded;
}

/**
 * Easier way for creating DOM elements
 */
export function dom( tagName, attributes = {}, ...children ) {
	const element = document.createElement( tagName );
	const events = [ 'click', 'change' ];
	for ( let attribute in attributes ) {
		if ( events.includes( attribute ) ) {
			element.addEventListener( attribute, attributes[attribute] );
		} else {
			element.setAttribute( attribute, attributes[attribute] );
		}
	}
	for ( let child of children ) {
		if ( child ) {
			element.appendChild( child );
		}
	}
	return element;
}

export const text = content => document.createTextNode( content );

/**
 * Returns a function that slides an element into/out of view within its container
 *
 * @param {string} containerSelector Global CSS selector for the container element with `overflow: hidden` holding the element to slide
 * @param {string} toExpandCollapseSelector Selector within the container of the element to slide into/out of view
 * @param {number} animationDuration How long should the animation last, in ms
 * @param {object} state Reference to a shared mutable animation state, should have at least a boolean `expanded` key
 */
export function createExpandCollapseCallback( containerSelector, toExpandCollapseSelector, animationDuration ) {
	return function() {
		if ( animating ) {
			return;
		}
		animating = true;
		if ( console.time ) {
			console.time( 'animation' );
		}
		const container = document.querySelector( containerSelector );
		const toExpandCollapse = container.querySelector( toExpandCollapseSelector );
		toExpandCollapse.style.display = 'block';
		let animation;
		if ( expanded ) {
			const moveUp = ( moved, height ) => -moved;
			animation = animateHeight( toExpandCollapse, animationDuration, moveUp );
		} else {
			const moveDown = ( moved, height ) => moved - height;
			animation = animateHeight( toExpandCollapse, animationDuration, moveDown );
		}
		animation.then( () => {
			animating = false;
			if ( console.timeEnd ) {
				console.timeEnd( 'animation' );
			}
			updateExpanded( ! expanded );
		} );
	};
}

function animateHeight( children, animationDuration, changeTop ) {
	const height = children.clientHeight;
	const frameDuration = 1/60 * 1000; // 60 fps
	return new Promise( ( resolve, reject ) => {
		const slideSomeMore = function( moved ) {
			let next = window.performance.now() + frameDuration;
			while ( moved < height ) {
				if ( window.performance.now() < next ) {
					continue;
				}
				children.style.top = `${ changeTop( moved, height ) }px`;
				moved = moved + height / animationDuration;
				setTimeout( () => slideSomeMore( moved ), 0 );
				return;
			}
			resolve();
		}
		slideSomeMore( 0 );
	} );
}
