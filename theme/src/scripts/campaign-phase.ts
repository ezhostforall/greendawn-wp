export interface CampaignTimeline {
	startsAt?: number | null;
	lateAt?: number | null;
	endsAt?: number | null;
}

export function campaignPhase(timeline: CampaignTimeline, now = Date.now() / 1000) {
	if (!timeline.endsAt || now >= timeline.endsAt) return 'closed';
	if (timeline.startsAt && now < timeline.startsAt) return 'upcoming';
	return timeline.lateAt && now >= timeline.lateAt ? 'late' : 'live';
}

export function campaignIsActive(timeline: CampaignTimeline) {
	const phase = campaignPhase(timeline);
	return phase === 'live' || phase === 'late';
}
