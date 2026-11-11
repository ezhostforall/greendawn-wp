const dialog = document.querySelector<HTMLDialogElement>('[data-gd-photo-dialog]');

if (dialog && typeof dialog.showModal === 'function') {
	const photo = dialog.querySelector('img');
	const caption = dialog.querySelector('figcaption');
	const previous = dialog.querySelector<HTMLButtonElement>('[data-gd-photo-prev]');
	const next = dialog.querySelector<HTMLButtonElement>('[data-gd-photo-next]');
	const original = dialog.querySelector<HTMLAnchorElement>('[data-gd-photo-original]');
	const position = dialog.querySelector('[data-gd-photo-position]');
	const error = dialog.querySelector<HTMLElement>('[data-gd-photo-error]');

	if (photo && caption && previous && next && original && position && error) {
		let links: HTMLAnchorElement[] = [];
		let index = 0;
		let trigger: HTMLAnchorElement | null = null;

		const show = (newIndex: number): void => {
			index = (newIndex + links.length) % links.length;
			const link = links[index];
			if (!link) return;
			photo.alt = link.querySelector('img')?.alt ?? '';
			photo.hidden = false;
			error.hidden = true;
			photo.src = link.href;
			caption.textContent = link.closest('figure')?.querySelector('figcaption')?.textContent ?? '';
			caption.hidden = !caption.textContent;
			original.href = link.href;
			position.textContent = `${index + 1} / ${links.length}`;
			previous.hidden = links.length < 2;
			next.hidden = links.length < 2;
		};

		for (const group of document.querySelectorAll('[data-gd-photo-group]')) {
			group.addEventListener('click', event => {
				if (!(event instanceof MouseEvent) || !(event.target instanceof Element)) return;
				if (event.button !== 0 || event.metaKey || event.ctrlKey || event.altKey || event.shiftKey) return;
				const link = event.target.closest<HTMLAnchorElement>('a[data-gd-photo]');
				if (!link || dialog.open) return;
				links = [...group.querySelectorAll<HTMLAnchorElement>('a[data-gd-photo]')];
				trigger = link;
				show(links.indexOf(link));
				dialog.showModal();
				event.preventDefault();
			});
		}

		previous.addEventListener('click', () => show(index - 1));
		next.addEventListener('click', () => show(index + 1));
		dialog.addEventListener('keydown', event => {
			if (links.length < 2 || event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
			if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
			event.preventDefault();
			show(index + (event.key === 'ArrowLeft' ? -1 : 1));
		});
		photo.addEventListener('error', () => {
			photo.hidden = true;
			error.hidden = false;
		});
		dialog.addEventListener('close', () => {
			photo.removeAttribute('src');
			trigger?.focus({ preventScroll: true });
			trigger = null;
		});
	}
}
