var code    = 'st=>start: Improve your l10n process!\n'
			+'e=>end: Continue to have fun!:>https://youtu.be/YQryHo1iHb8[blank]\n'
			+'op1=>operation: Go to locize.com:>https://locize.com[blank]\n'
			+'sub1=>subroutine: Read the awesomeness\n'
			+'cond(align-next=no)=>condition: Interested to\n'
			+'getting started?\n'
			+'io=>inputoutput: Register:>https://www.locize.io/register[blank]\n'
			+'sub2=>subroutine: Read about improving\n'
			+'your localization workflow\n'
			+'or another source:>https://medium.com/@adrai/8-signs-you-should-improve-your-localization-process-3dc075d53998[blank]\n'
			+'op2=>operation: Login:>https://www.locize.io/login[blank]\n'
			+'cond2=>condition: valid password?\n'
			+'cond3=>condition: reset password?\n'
			+'op3=>operation: send email\n'
			+'sub3=>subroutine: Create a demo project\n'
			+'sub4=>subroutine: Start your real project\n'
			+'io2=>inputoutput: Subscribe\n'
			+'st->op1->sub1->cond\n'
			+'cond(yes)->io->op2->cond2\n'
			+'cond2(no)->cond3\n'
			+'cond3(no,bottom)->op2\n'
			+'cond3(yes)->op3\n'
			+'op3(right)->op2\n'
			+'cond2(yes)->sub3\n'
			+'sub3->sub4->io2->e\n'
			+'cond(no)->sub2(right)->op1\n'
			+'st@>op1({"stroke":"Red"})@>sub1({"stroke":"Red"})@>cond({"stroke":"Red"})@>io({"stroke":"Red"})@>op2({"stroke":"Red"})@>cond2({"stroke":"Red"})@>sub3({"stroke":"Red"})@>sub4({"stroke":"Red"})@>io2({"stroke":"Red"})@>e({"stroke":"Red","stroke-width":6,"arrow-end":"classic-wide-long"})'
         ;

////////////////////////////////////
// var code    = 'st=>start: Start\n'
// 			+ 'e=>end\n'
// 			+ 'op=>operation: My Operation|start\n'
// 			+ 'cond=>condition: Yes or No?\n'

// 			+ 'st->op->cond\n'
// 			+ 'cond(yes)->e\n'
// 			+ 'cond(no)->op\n'
// ;
//////////////////////////////////

var diagram = flowchart.parse(code);
diagram.drawSVG('diagram', {
		'x': 0,
		'y': 0,
		'line-width': 3,
		'line-length': 50,
		'text-margin': 20,
		'font-size': 14,
		'font-color': 'black',
		'line-color': 'black',
		'element-color': 'black',
		'fill': 'white',
		'yes-text': 'yes',
		'no-text': 'no',
		'arrow-end': 'block',
		'scale': 1,
		// style symbol types
		'symbols': {
			'start': {
			  'font-color': 'red',
			  'element-color': 'green',
			  'fill': 'yellow'
			},
			'end':{
			  'class': 'end-element'
			}
		},
	'flowstate' : {
		'past' : { 'fill' : '#CCCCCC', 'font-size' : 12},
		'current' : {'fill' : 'yellow', 'font-color' : 'red', 'font-weight' : 'bold', 'element-color' : 'red'},
		'future' : { 'fill' : 'white'},
		'start' : { 'fill' : 'blue'},
		'invalid': {'fill' : '#444444'},
		'approved' : { 'fill' : '#58C4A3', 'font-size' : 12, 'yes-text' : 'APPROVED', 'no-text' : 'n/a' },
		'rejected' : { 'fill' : '#C45879', 'font-size' : 12, 'yes-text' : 'n/a', 'no-text' : 'REJECTED' }
	}});