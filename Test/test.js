function keyf(doc) {
	return {
		push : 0,
		fraud_push : 0,
		after_push : 0,
		after_require : 0,
		date_point : new Date(parseInt(doc.date) * 1000).getFullYear() + '-'
				+ parseInt(new Date(parseInt(doc.date) * 1000).getMonth() + 1)
				+ '-' + new Date(parseInt(doc.date) * 1000).getDate(),
		media_id : doc.websiteid,
		after_show : 0,
		show : 0,
		show_surplus : 0,
		fraud_show : 0,
		require : 0,
		fraud_require : 0,
		count : 0
	};
}

function reduce(doc, out) {
	out.push += (doc.push ? doc.push : 0);
	out.fraud_push += (doc.fraud_push ? doc.fraud_push : 0);
	out.show += (doc.show ? doc.show : 0);
	out.fraud_show += (doc.fraud_show ? doc.fraud_show : 0);
	out.require += (doc.require ? doc.require : 0);
	out.fraud_require += (doc.fraud_require ? doc.fraud_require : 0);
	out.count++;
}

function finalize(out) {
	out.after_push = (out.push ? out.push : 0)
			- (out.fraud_push ? out.fraud_push : 0);
	out.after_push = out.after_push == Infinity ? 0 : out.after_push;
	out.after_require = (out.require ? out.require : 0)
			- (out.fraud_require ? out.fraud_require : 0);
	out.after_require = out.after_require == Infinity ? 0 : out.after_require;
	out.after_show = (out.show ? out.show : 0)
			- (out.fraud_show ? out.fraud_show : 0);
	out.after_show = out.after_show == Infinity ? 0 : out.after_show;
	out.show_surplus = (out.after_show ? out.after_show : 0)
			+ (out.after_require ? out.after_require : 0)
			- (out.after_push ? out.after_push : 0);
	out.show_surplus = out.show_surplus == Infinity ? 0 : out.show_surplus;
}