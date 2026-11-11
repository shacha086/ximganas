--- net/iflib.c.orig	2025-10-17 07:47:26.190336000 +0200
+++ net/iflib.c	2025-10-17 12:06:14.000000000 +0200
@@ -70,6 +70,7 @@
 #include <netinet/ip.h>
 #include <netinet/ip6.h>
 #include <netinet/tcp.h>
+#include <netinet/udp.h>
 #include <netinet/ip_var.h>
 #include <netinet6/ip6_var.h>
 
@@ -141,6 +142,9 @@
 static void iru_init(if_rxd_update_t iru, iflib_rxq_t rxq, uint8_t flid);
 static void iflib_timer(void *arg);
 static void iflib_tqg_detach(if_ctx_t ctx);
+#ifndef ALTQ
+static int  iflib_simple_transmit(if_t ifp, struct mbuf *m);
+#endif
 
 typedef struct iflib_filter_info {
 	driver_filter_t *ifi_filter;
@@ -196,7 +200,8 @@
 	uint8_t  ifc_sysctl_separate_txrx;
 	uint8_t  ifc_sysctl_use_logical_cores;
 	uint16_t ifc_sysctl_extra_msix_vectors;
-	bool	 ifc_cpus_are_physical_cores;
+	bool     ifc_cpus_are_physical_cores;
+	bool     ifc_sysctl_simple_tx;
 
 	qidx_t ifc_sysctl_ntxds[8];
 	qidx_t ifc_sysctl_nrxds[8];
@@ -281,16 +286,16 @@
 #define CTX_IS_VF(ctx)		((ctx)->ifc_sctx->isc_flags & IFLIB_IS_VF)
 
 typedef struct iflib_sw_rx_desc_array {
-	bus_dmamap_t	*ifsd_map;         /* bus_dma maps for packet */
-	struct mbuf	**ifsd_m;           /* pkthdr mbufs */
-	caddr_t		*ifsd_cl;          /* direct cluster pointer for rx */
-	bus_addr_t	*ifsd_ba;          /* bus addr of cluster for rx */
+	bus_dmamap_t	*ifsd_map;	/* bus_dma maps for packet */
+	struct mbuf	**ifsd_m;	/* pkthdr mbufs */
+	caddr_t		*ifsd_cl;	/* direct cluster pointer for rx */
+	bus_addr_t	*ifsd_ba;	/* bus addr of cluster for rx */
 } iflib_rxsd_array_t;
 
 typedef struct iflib_sw_tx_desc_array {
-	bus_dmamap_t    *ifsd_map;         /* bus_dma maps for packet */
-	bus_dmamap_t	*ifsd_tso_map;     /* bus_dma maps for TSO packet */
-	struct mbuf    **ifsd_m;           /* pkthdr mbufs */
+	bus_dmamap_t	*ifsd_map;	/* bus_dma maps for packet */
+	bus_dmamap_t	*ifsd_tso_map;	/* bus_dma maps for TSO packet */
+	struct mbuf	**ifsd_m;	/* pkthdr mbufs */
 } if_txsd_vec_t;
 
 /* magic number that should be high enough for any hardware */
@@ -363,7 +368,7 @@
 
 	/* constant values */
 	if_ctx_t	ift_ctx;
-	struct ifmp_ring        *ift_br;
+	struct ifmp_ring	*ift_br;
 	struct grouptask	ift_task;
 	qidx_t		ift_size;
 	uint16_t	ift_id;
@@ -381,7 +386,7 @@
 	bus_dma_tag_t	ift_tso_buf_tag;
 	iflib_dma_info_t	ift_ifdi;
 #define	MTX_NAME_LEN	32
-	char                    ift_mtx_name[MTX_NAME_LEN];
+	char		ift_mtx_name[MTX_NAME_LEN];
 	bus_dma_segment_t	ift_segs[IFLIB_MAX_TX_SEGS]  __aligned(CACHE_LINE_SIZE);
 #ifdef IFLIB_DIAGNOSTICS
 	uint64_t ift_cpu_exec_count[256];
@@ -401,7 +406,7 @@
 	uint64_t	ifl_cl_dequeued;
 #endif
 	/* implicit pad */
-	bitstr_t 	*ifl_rx_bitmap;
+	bitstr_t	*ifl_rx_bitmap;
 	qidx_t		ifl_fragidx;
 	/* constant */
 	qidx_t		ifl_size;
@@ -457,7 +462,7 @@
 	uint8_t		ifr_txqid[IFLIB_MAX_TX_SHARED_INTR];
 	uint8_t		ifr_fl_offset;
 	struct lro_ctrl		ifr_lc;
-	struct grouptask        ifr_task;
+	struct grouptask	ifr_task;
 	struct callout		ifr_watchdog;
 	struct iflib_filter_info ifr_filter_info;
 	iflib_dma_info_t	ifr_ifdi;
@@ -548,7 +553,7 @@
 #define STATE_LOCK_DESTROY(ctx)	mtx_destroy(&(ctx)->ifc_state_mtx)
 
 #define CALLOUT_LOCK(txq)	mtx_lock(&txq->ift_mtx)
-#define CALLOUT_UNLOCK(txq) 	mtx_unlock(&txq->ift_mtx)
+#define CALLOUT_UNLOCK(txq)	mtx_unlock(&txq->ift_mtx)
 
 /* Our boot-time initialization hook */
 static int	iflib_module_event_handler(module_t, int, void *);
@@ -709,7 +714,7 @@
 static void iflib_altq_if_start(if_t ifp);
 static int iflib_altq_if_transmit(if_t ifp, struct mbuf *m);
 #endif
-static int iflib_register(if_ctx_t);
+static void iflib_register(if_ctx_t);
 static void iflib_deregister(if_ctx_t);
 static void iflib_unregister_vlan_handlers(if_ctx_t ctx);
 static uint16_t iflib_get_mbuf_size_for(unsigned int size);
@@ -724,6 +729,7 @@
 #ifndef __NO_STRICT_ALIGNMENT
 static struct mbuf *iflib_fixup_rx(struct mbuf *m);
 #endif
+static __inline int iflib_completed_tx_reclaim(iflib_txq_t txq, int thresh);
 
 static SLIST_HEAD(cpu_offset_list, cpu_offset) cpu_offsets =
     SLIST_HEAD_INITIALIZER(cpu_offsets);
@@ -926,7 +932,7 @@
 			MPASS(i < IFLIB_MAX_RX_REFRESH);
 
 			if (addr == NETMAP_BUF_BASE(na)) /* bad buf */
-			        return (netmap_ring_reinit(kring));
+				return (netmap_ring_reinit(kring));
 
 			fl->ifl_bus_addrs[i] = paddr +
 			    nm_get_offset(kring, slot);
@@ -1457,17 +1463,17 @@
 	lowaddr = DMA_WIDTH_TO_BUS_LOWADDR(ctx->ifc_softc_ctx.isc_dma_width);
 
 	err = bus_dma_tag_create(bus_get_dma_tag(dev),	/* parent */
-				align, 0,		/* alignment, bounds */
-				lowaddr,		/* lowaddr */
-				BUS_SPACE_MAXADDR,	/* highaddr */
-				NULL, NULL,		/* filter, filterarg */
-				size,			/* maxsize */
-				1,			/* nsegments */
-				size,			/* maxsegsize */
-				BUS_DMA_ALLOCNOW,	/* flags */
-				NULL,			/* lockfunc */
-				NULL,			/* lockarg */
-				&dma->idi_tag);
+		    align, 0,		/* alignment, bounds */
+		    lowaddr,		/* lowaddr */
+		    BUS_SPACE_MAXADDR,	/* highaddr */
+		    NULL, NULL,		/* filter, filterarg */
+		    size,		/* maxsize */
+		    1,			/* nsegments */
+		    size,		/* maxsegsize */
+		    BUS_DMA_ALLOCNOW,	/* flags */
+		    NULL,		/* lockfunc */
+		    NULL,		/* lockarg */
+		    &dma->idi_tag);
 	if (err) {
 		device_printf(dev,
 		    "%s: bus_dma_tag_create failed: %d (size=%d, align=%d)\n",
@@ -1679,11 +1685,11 @@
 	irq->ii_res = res;
 	KASSERT(filter == NULL || handler == NULL, ("filter and handler can't both be non-NULL"));
 	rc = bus_setup_intr(dev, res, INTR_MPSAFE | INTR_TYPE_NET,
-						filter, handler, arg, &tag);
+		    filter, handler, arg, &tag);
 	if (rc != 0) {
 		device_printf(dev,
 		    "failed to setup interrupt for rid %d, name %s: %d\n",
-					  rid, name ? name : "unknown", rc);
+		    rid, name ? name : "unknown", rc);
 		return (rc);
 	} else if (name)
 		bus_describe_intr(dev, res, tag, "%s", name);
@@ -1732,17 +1738,17 @@
 	 * Set up DMA tags for TX buffers.
 	 */
 	if ((err = bus_dma_tag_create(bus_get_dma_tag(dev),
-			       1, 0,			/* alignment, bounds */
-			       lowaddr,			/* lowaddr */
-			       BUS_SPACE_MAXADDR,	/* highaddr */
-			       NULL, NULL,		/* filter, filterarg */
-			       sctx->isc_tx_maxsize,		/* maxsize */
-			       nsegments,	/* nsegments */
-			       sctx->isc_tx_maxsegsize,	/* maxsegsize */
-			       0,			/* flags */
-			       NULL,			/* lockfunc */
-			       NULL,			/* lockfuncarg */
-			       &txq->ift_buf_tag))) {
+		    1, 0,			/* alignment, bounds */
+		    lowaddr,			/* lowaddr */
+		    BUS_SPACE_MAXADDR,		/* highaddr */
+		    NULL, NULL,			/* filter, filterarg */
+		    sctx->isc_tx_maxsize,	/* maxsize */
+		    nsegments,			/* nsegments */
+		    sctx->isc_tx_maxsegsize,	/* maxsegsize */
+		    0,				/* flags */
+		    NULL,			/* lockfunc */
+		    NULL,			/* lockfuncarg */
+		    &txq->ift_buf_tag))) {
 		device_printf(dev, "Unable to allocate TX DMA tag: %d\n", err);
 		device_printf(dev, "maxsize: %ju nsegments: %d maxsegsize: %ju\n",
 		    (uintmax_t)sctx->isc_tx_maxsize, nsegments, (uintmax_t)sctx->isc_tx_maxsegsize);
@@ -1750,17 +1756,17 @@
 	}
 	tso = (if_getcapabilities(ctx->ifc_ifp) & IFCAP_TSO) != 0;
 	if (tso && (err = bus_dma_tag_create(bus_get_dma_tag(dev),
-			       1, 0,			/* alignment, bounds */
-			       lowaddr,			/* lowaddr */
-			       BUS_SPACE_MAXADDR,	/* highaddr */
-			       NULL, NULL,		/* filter, filterarg */
-			       tsomaxsize,		/* maxsize */
-			       ntsosegments,	/* nsegments */
-			       sctx->isc_tso_maxsegsize,/* maxsegsize */
-			       0,			/* flags */
-			       NULL,			/* lockfunc */
-			       NULL,			/* lockfuncarg */
-			       &txq->ift_tso_buf_tag))) {
+		    1, 0,			/* alignment, bounds */
+		    lowaddr,			/* lowaddr */
+		    BUS_SPACE_MAXADDR,		/* highaddr */
+		    NULL, NULL,			/* filter, filterarg */
+		    tsomaxsize,			/* maxsize */
+		    ntsosegments,		/* nsegments */
+		    sctx->isc_tso_maxsegsize,	/* maxsegsize */
+		    0,				/* flags */
+		    NULL,			/* lockfunc */
+		    NULL,			/* lockfuncarg */
+		    &txq->ift_tso_buf_tag))) {
 		device_printf(dev, "Unable to allocate TSO TX DMA tag: %d\n",
 		    err);
 		goto fail;
@@ -1966,17 +1972,17 @@
 		fl->ifl_size = scctx->isc_nrxd[rxq->ifr_fl_offset]; /* this isn't necessarily the same */
 		/* Set up DMA tag for RX buffers. */
 		err = bus_dma_tag_create(bus_get_dma_tag(dev), /* parent */
-					 1, 0,			/* alignment, bounds */
-					 lowaddr,		/* lowaddr */
-					 BUS_SPACE_MAXADDR,	/* highaddr */
-					 NULL, NULL,		/* filter, filterarg */
-					 sctx->isc_rx_maxsize,	/* maxsize */
-					 sctx->isc_rx_nsegments,	/* nsegments */
-					 sctx->isc_rx_maxsegsize,	/* maxsegsize */
-					 0,			/* flags */
-					 NULL,			/* lockfunc */
-					 NULL,			/* lockarg */
-					 &fl->ifl_buf_tag);
+			    1, 0,			/* alignment, bounds */
+			    lowaddr,			/* lowaddr */
+			    BUS_SPACE_MAXADDR,		/* highaddr */
+			    NULL, NULL,			/* filter, filterarg */
+			    sctx->isc_rx_maxsize,	/* maxsize */
+			    sctx->isc_rx_nsegments,	/* nsegments */
+			    sctx->isc_rx_maxsegsize,	/* maxsegsize */
+			    0,				/* flags */
+			    NULL,			/* lockfunc */
+			    NULL,			/* lockarg */
+			    &fl->ifl_buf_tag);
 		if (err) {
 			device_printf(dev,
 			    "Unable to allocate RX DMA tag: %d\n", err);
@@ -1995,8 +2001,8 @@
 
 		/* Allocate memory for the direct RX cluster pointer map. */
 		if (!(fl->ifl_sds.ifsd_cl =
-		      (caddr_t *) malloc(sizeof(caddr_t) *
-					      scctx->isc_nrxd[rxq->ifr_fl_offset], M_IFLIB, M_NOWAIT | M_ZERO))) {
+		    (caddr_t *) malloc(sizeof(caddr_t) *
+			    scctx->isc_nrxd[rxq->ifr_fl_offset], M_IFLIB, M_NOWAIT | M_ZERO))) {
 			device_printf(dev,
 			    "Unable to allocate RX cluster map memory\n");
 			err = ENOMEM;
@@ -2005,8 +2011,8 @@
 
 		/* Allocate memory for the RX cluster bus address map. */
 		if (!(fl->ifl_sds.ifsd_ba =
-		      (bus_addr_t *) malloc(sizeof(bus_addr_t) *
-					      scctx->isc_nrxd[rxq->ifr_fl_offset], M_IFLIB, M_NOWAIT | M_ZERO))) {
+		    (bus_addr_t *) malloc(sizeof(bus_addr_t) *
+			    scctx->isc_nrxd[rxq->ifr_fl_offset], M_IFLIB, M_NOWAIT | M_ZERO))) {
 			device_printf(dev,
 			    "Unable to allocate RX bus address map memory\n");
 			err = ENOMEM;
@@ -2017,7 +2023,7 @@
 		 * Create the DMA maps for RX buffers.
 		 */
 		if (!(fl->ifl_sds.ifsd_map =
-		      (bus_dmamap_t *) malloc(sizeof(bus_dmamap_t) * scctx->isc_nrxd[rxq->ifr_fl_offset], M_IFLIB, M_NOWAIT | M_ZERO))) {
+		    (bus_dmamap_t *) malloc(sizeof(bus_dmamap_t) * scctx->isc_nrxd[rxq->ifr_fl_offset], M_IFLIB, M_NOWAIT | M_ZERO))) {
 			device_printf(dev,
 			    "Unable to allocate RX buffer DMA map memory\n");
 			err = ENOMEM;
@@ -2557,7 +2563,7 @@
 		callout_reset_on(&txq->ift_timer, iflib_timer_default, iflib_timer, txq,
 			txq->ift_timer.c_cpu);
 
-        /* Re-enable txsync/rxsync. */
+	/* Re-enable txsync/rxsync. */
 	netmap_enable_all_rings(ifp);
 }
 
@@ -2623,8 +2629,10 @@
 #endif /* DEV_NETMAP */
 		CALLOUT_UNLOCK(txq);
 
-		/* clean any enqueued buffers */
-		iflib_ifmp_purge(txq);
+		if (!ctx->ifc_sysctl_simple_tx) {
+			/* clean any enqueued buffers */
+			iflib_ifmp_purge(txq);
+		}
 		/* Free any existing tx buffers. */
 		for (j = 0; j < txq->ift_size; j++) {
 			iflib_txsd_free(ctx, txq, j);
@@ -2889,52 +2897,7 @@
 	return (m);
 }
 
-#if defined(INET6) || defined(INET)
 static void
-iflib_get_ip_forwarding(struct lro_ctrl *lc, bool *v4, bool *v6)
-{
-	CURVNET_SET(if_getvnet(lc->ifp));
-#if defined(INET6)
-	*v6 = V_ip6_forwarding;
-#endif
-#if defined(INET)
-	*v4 = V_ipforwarding;
-#endif
-	CURVNET_RESTORE();
-}
-
-/*
- * Returns true if it's possible this packet could be LROed.
- * if it returns false, it is guaranteed that tcp_lro_rx()
- * would not return zero.
- */
-static bool
-iflib_check_lro_possible(struct mbuf *m, bool v4_forwarding, bool v6_forwarding)
-{
-	struct ether_header *eh;
-
-	eh = mtod(m, struct ether_header *);
-	switch (eh->ether_type) {
-#if defined(INET6)
-	case htons(ETHERTYPE_IPV6):
-		return (!v6_forwarding);
-#endif
-#if defined(INET)
-	case htons(ETHERTYPE_IP):
-		return (!v4_forwarding);
-#endif
-	}
-
-	return (false);
-}
-#else
-static void
-iflib_get_ip_forwarding(struct lro_ctrl *lc __unused, bool *v4 __unused, bool *v6 __unused)
-{
-}
-#endif
-
-static void
 _task_fn_rx_watchdog(void *context)
 {
 	iflib_rxq_t rxq = context;
@@ -2954,19 +2917,19 @@
 	struct if_rxd_info ri;
 	int err, budget_left, rx_bytes, rx_pkts;
 	iflib_fl_t fl;
+#if defined(INET6) || defined(INET)
 	int lro_enabled;
-	bool v4_forwarding, v6_forwarding, lro_possible;
+#endif
 	uint8_t retval = 0;
 
 	/*
 	 * XXX early demux data packets so that if_input processing only handles
 	 * acks in interrupt context
 	 */
-	struct mbuf *m, *mh, *mt, *mf;
+	struct mbuf *m, *mh, *mt;
 
 	NET_EPOCH_ASSERT();
 
-	lro_possible = v4_forwarding = v6_forwarding = false;
 	ifp = ctx->ifc_ifp;
 	mh = mt = NULL;
 	MPASS(budget > 0);
@@ -2982,6 +2945,10 @@
 		return (retval);
 	}
 
+#if defined(INET6) || defined(INET)
+	lro_enabled = (if_getcapenable(ifp) & IFCAP_LRO);
+#endif
+
 	/* pfil needs the vnet to be set */
 	CURVNET_SET_QUIET(if_getvnet(ifp));
 	for (budget_left = budget; budget_left > 0 && avail > 0;) {
@@ -3026,7 +2993,17 @@
 		if (__predict_false(m == NULL))
 			continue;
 
-		/* imm_pkt: -- cxgb */
+#ifndef __NO_STRICT_ALIGNMENT
+		if (!IP_ALIGNED(m) && (m = iflib_fixup_rx(m)) == NULL)
+			continue;
+#endif
+#if defined(INET6) || defined(INET)
+		if (lro_enabled) {
+			tcp_lro_queue_mbuf(&rxq->ifr_lc, m);
+			continue;
+		}
+#endif
+
 		if (mh == NULL)
 			mh = mt = m;
 		else {
@@ -3039,49 +3016,8 @@
 	for (i = 0, fl = &rxq->ifr_fl[0]; i < sctx->isc_nfl; i++, fl++)
 		retval |= iflib_fl_refill_all(ctx, fl);
 
-	lro_enabled = (if_getcapenable(ifp) & IFCAP_LRO);
-	if (lro_enabled)
-		iflib_get_ip_forwarding(&rxq->ifr_lc, &v4_forwarding, &v6_forwarding);
-	mt = mf = NULL;
-	while (mh != NULL) {
-		m = mh;
-		mh = mh->m_nextpkt;
-		m->m_nextpkt = NULL;
-#ifndef __NO_STRICT_ALIGNMENT
-		if (!IP_ALIGNED(m) && (m = iflib_fixup_rx(m)) == NULL)
-			continue;
-#endif
-#if defined(INET6) || defined(INET)
-		if (lro_enabled) {
-			if (!lro_possible) {
-				lro_possible = iflib_check_lro_possible(m, v4_forwarding, v6_forwarding);
-				if (lro_possible && mf != NULL) {
-					if_input(ifp, mf);
-					DBG_COUNTER_INC(rx_if_input);
-					mt = mf = NULL;
-				}
-			}
-			if ((m->m_pkthdr.csum_flags & (CSUM_L4_CALC | CSUM_L4_VALID)) ==
-			    (CSUM_L4_CALC | CSUM_L4_VALID)) {
-				if (lro_possible && tcp_lro_rx(&rxq->ifr_lc, m, 0) == 0)
-					continue;
-			}
-		}
-#endif
-		if (lro_possible) {
-			if_input(ifp, m);
-			DBG_COUNTER_INC(rx_if_input);
-			continue;
-		}
-
-		if (mf == NULL)
-			mf = m;
-		if (mt != NULL)
-			mt->m_nextpkt = m;
-		mt = m;
-	}
-	if (mf != NULL) {
-		if_input(ifp, mf);
+	if (mh != NULL) {
+		if_input(ifp, mh);
 		DBG_COUNTER_INC(rx_if_input);
 	}
 
@@ -3188,11 +3124,11 @@
 print_pkt(if_pkt_info_t pi)
 {
 	printf("pi len:  %d qsidx: %d nsegs: %d ndescs: %d flags: %x pidx: %d\n",
-	       pi->ipi_len, pi->ipi_qsidx, pi->ipi_nsegs, pi->ipi_ndescs, pi->ipi_flags, pi->ipi_pidx);
+	    pi->ipi_len, pi->ipi_qsidx, pi->ipi_nsegs, pi->ipi_ndescs, pi->ipi_flags, pi->ipi_pidx);
 	printf("pi new_pidx: %d csum_flags: %lx tso_segsz: %d mflags: %x vtag: %d\n",
-	       pi->ipi_new_pidx, pi->ipi_csum_flags, pi->ipi_tso_segsz, pi->ipi_mflags, pi->ipi_vtag);
+	    pi->ipi_new_pidx, pi->ipi_csum_flags, pi->ipi_tso_segsz, pi->ipi_mflags, pi->ipi_vtag);
 	printf("pi etype: %d ehdrlen: %d ip_hlen: %d ipproto: %d\n",
-	       pi->ipi_etype, pi->ipi_ehdrlen, pi->ipi_ip_hlen, pi->ipi_ipproto);
+	    pi->ipi_etype, pi->ipi_ehdrlen, pi->ipi_ip_hlen, pi->ipi_ipproto);
 }
 #endif
 
@@ -3372,43 +3308,29 @@
 #ifdef INET
 	case ETHERTYPE_IP:
 	{
-		struct mbuf *n;
-		struct ip *ip = NULL;
-		struct tcphdr *th = NULL;
-		int minthlen;
+		struct ip *ip;
+		struct tcphdr *th;
+		uint8_t hlen;
 
-		minthlen = min(m->m_pkthdr.len, pi->ipi_ehdrlen + sizeof(*ip) + sizeof(*th));
-		if (__predict_false(m->m_len < minthlen)) {
-			/*
-			 * if this code bloat is causing too much of a hit
-			 * move it to a separate function and mark it noinline
-			 */
-			if (m->m_len == pi->ipi_ehdrlen) {
-				n = m->m_next;
-				MPASS(n);
-				if (n->m_len >= sizeof(*ip))  {
-					ip = (struct ip *)n->m_data;
-					if (n->m_len >= (ip->ip_hl << 2) + sizeof(*th))
-						th = (struct tcphdr *)((caddr_t)ip + (ip->ip_hl << 2));
-				} else {
-					txq->ift_pullups++;
-					if (__predict_false((m = m_pullup(m, minthlen)) == NULL))
-						return (ENOMEM);
-					ip = (struct ip *)(m->m_data + pi->ipi_ehdrlen);
-				}
-			} else {
-				txq->ift_pullups++;
-				if (__predict_false((m = m_pullup(m, minthlen)) == NULL))
-					return (ENOMEM);
-				ip = (struct ip *)(m->m_data + pi->ipi_ehdrlen);
-				if (m->m_len >= (ip->ip_hl << 2) + sizeof(*th))
-					th = (struct tcphdr *)((caddr_t)ip + (ip->ip_hl << 2));
-			}
-		} else {
-			ip = (struct ip *)(m->m_data + pi->ipi_ehdrlen);
-			if (m->m_len >= (ip->ip_hl << 2) + sizeof(*th))
-				th = (struct tcphdr *)((caddr_t)ip + (ip->ip_hl << 2));
+		hlen = pi->ipi_ehdrlen + sizeof(*ip);
+		if (__predict_false(m->m_len < hlen)) {
+			txq->ift_pullups++;
+			if (__predict_false((m = m_pullup(m, hlen)) == NULL))
+				return (ENOMEM);
 		}
+		ip = (struct ip *)(m->m_data + pi->ipi_ehdrlen);
+		hlen = pi->ipi_ehdrlen + (ip->ip_hl << 2);
+		if (ip->ip_p == IPPROTO_TCP) {
+			hlen += sizeof(*th);
+			th = (struct tcphdr *)((char *)ip + (ip->ip_hl << 2));
+		} else if (ip->ip_p == IPPROTO_UDP) {
+			hlen += sizeof(struct udphdr);
+		}
+		if (__predict_false(m->m_len < hlen)) {
+			txq->ift_pullups++;
+			if ((m = m_pullup(m, hlen)) == NULL)
+				return (ENOMEM);
+		}
 		pi->ipi_ip_hlen = ip->ip_hl << 2;
 		pi->ipi_ipproto = ip->ip_p;
 		pi->ipi_ip_tos = ip->ip_tos;
@@ -3417,12 +3339,6 @@
 		/* TCP checksum offload may require TCP header length */
 		if (IS_TX_OFFLOAD4(pi)) {
 			if (__predict_true(pi->ipi_ipproto == IPPROTO_TCP)) {
-				if (__predict_false(th == NULL)) {
-					txq->ift_pullups++;
-					if (__predict_false((m = m_pullup(m, (ip->ip_hl << 2) + sizeof(*th))) == NULL))
-						return (ENOMEM);
-					th = (struct tcphdr *)((caddr_t)ip + pi->ipi_ip_hlen);
-				}
 				pi->ipi_tcp_hflags = th->th_flags;
 				pi->ipi_tcp_hlen = th->th_off << 2;
 				pi->ipi_tcp_seq = th->th_seq;
@@ -3726,10 +3642,22 @@
 	 *        cxgb
 	 */
 	if (__predict_false(nsegs + 2 > TXQ_AVAIL(txq))) {
-		txq->ift_no_desc_avail++;
-		bus_dmamap_unload(buf_tag, map);
-		DBG_COUNTER_INC(encap_txq_avail_fail);
-		DBG_COUNTER_INC(encap_txd_encap_fail);
+		(void)iflib_completed_tx_reclaim(txq, RECLAIM_THRESH(ctx));
+		if (__predict_false(nsegs + 2 > TXQ_AVAIL(txq))) {
+			txq->ift_no_desc_avail++;
+			bus_dmamap_unload(buf_tag, map);
+			DBG_COUNTER_INC(encap_txq_avail_fail);
+			DBG_COUNTER_INC(encap_txd_encap_fail);
+			if (ctx->ifc_sysctl_simple_tx) {
+				*m_headp = m_head = iflib_remove_mbuf(txq);
+				m_freem(*m_headp);
+				DBG_COUNTER_INC(tx_frees);
+				*m_headp = NULL;
+			}
+			if ((txq->ift_task.gt_task.ta_flags & TASK_ENQUEUED) == 0)
+				GROUPTASK_ENQUEUE(&txq->ift_task);
+			return (ENOBUFS);
+		}
 		if ((txq->ift_task.gt_task.ta_flags & TASK_ENQUEUED) == 0)
 			GROUPTASK_ENQUEUE(&txq->ift_task);
 		return (ENOBUFS);
@@ -3742,7 +3670,7 @@
 	 */
 	txq->ift_rs_pending += nsegs + 1;
 	if (txq->ift_rs_pending > TXQ_MAX_RS_DEFERRED(txq) ||
-	     iflib_no_tx_batch || (TXQ_AVAIL(txq) - nsegs) <= MAX_TX_DESC(ctx) + 2) {
+	    iflib_no_tx_batch || (TXQ_AVAIL(txq) - nsegs) <= MAX_TX_DESC(ctx) + 2) {
 		pi.ipi_flags |= IPI_TX_INTR;
 		txq->ift_rs_pending = 0;
 	}
@@ -3878,9 +3806,8 @@
 #ifdef INVARIANTS
 		if (iflib_verbose_debug) {
 			printf("%s processed=%ju cleaned=%ju tx_nsegments=%d reclaim=%d thresh=%d\n", __func__,
-			       txq->ift_processed, txq->ift_cleaned, txq->ift_ctx->ifc_softc_ctx.isc_tx_nsegments,
-			       reclaim, thresh);
-		}
+			    txq->ift_processed, txq->ift_cleaned, txq->ift_ctx->ifc_softc_ctx.isc_tx_nsegments,
+			    reclaim, thresh);		}
 #endif
 		return (0);
 	}
@@ -3984,7 +3911,7 @@
 #ifdef INVARIANTS
 	if (iflib_verbose_debug)
 		printf("%s avail=%d ifc_flags=%x txq_avail=%d ", __func__,
-		       avail, ctx->ifc_flags, TXQ_AVAIL(txq));
+		    avail, ctx->ifc_flags, TXQ_AVAIL(txq));
 #endif
 	do_prefetch = (ctx->ifc_flags & IFC_PREFETCH);
 	err = 0;
@@ -4105,6 +4032,12 @@
 	    netmap_tx_irq(ifp, txq->ift_id))
 		goto skip_ifmp;
 #endif
+        if (ctx->ifc_sysctl_simple_tx) {
+                mtx_lock(&txq->ift_mtx);
+                (void)iflib_completed_tx_reclaim(txq, RECLAIM_THRESH(ctx));
+                mtx_unlock(&txq->ift_mtx);
+                goto skip_ifmp;
+        }
 #ifdef ALTQ
 	if (if_altq_is_enabled(ifp))
 		iflib_altq_if_start(ifp);
@@ -4118,9 +4051,9 @@
 	 */
 	if (abdicate)
 		ifmp_ring_check_drainage(txq->ift_br, TX_BATCH_SIZE);
-#ifdef DEV_NETMAP
+
 skip_ifmp:
-#endif
+
 	if (ctx->ifc_flags & IFC_LEGACY)
 		IFDI_INTR_ENABLE(ctx);
 	else
@@ -4376,6 +4309,10 @@
 		ifmp_ring_check_drainage(txq->ift_br, TX_BATCH_SIZE);
 		m_freem(m);
 		DBG_COUNTER_INC(tx_frees);
+		if (err == ENOBUFS)
+			if_inc_counter(ifp, IFCOUNTER_OQDROPS, 1);
+		else
+			if_inc_counter(ifp, IFCOUNTER_OERRORS, 1);
 	}
 
 	return (err);
@@ -4459,10 +4396,9 @@
 }
 
 #define IFCAP_FLAGS (IFCAP_HWCSUM_IPV6 | IFCAP_HWCSUM | IFCAP_LRO | \
-		     IFCAP_TSO | IFCAP_VLAN_HWTAGGING | IFCAP_HWSTATS | \
-		     IFCAP_VLAN_MTU | IFCAP_VLAN_HWFILTER | \
-		     IFCAP_VLAN_HWTSO | IFCAP_VLAN_HWCSUM | IFCAP_MEXTPG)
-
+		    IFCAP_TSO | IFCAP_VLAN_HWTAGGING | IFCAP_HWSTATS | \
+		    IFCAP_VLAN_MTU | IFCAP_VLAN_HWFILTER | \
+		    IFCAP_VLAN_HWTSO | IFCAP_VLAN_HWCSUM | IFCAP_MEXTPG)
 static int
 iflib_if_ioctl(if_t ifp, u_long command, caddr_t data)
 {
@@ -4807,17 +4743,17 @@
 	for (i = 0; i < sctx->isc_nrxqs; i++) {
 		if (scctx->isc_nrxd[i] < sctx->isc_nrxd_min[i]) {
 			device_printf(dev, "nrxd%d: %d less than nrxd_min %d - resetting to min\n",
-				      i, scctx->isc_nrxd[i], sctx->isc_nrxd_min[i]);
+			    i, scctx->isc_nrxd[i], sctx->isc_nrxd_min[i]);
 			scctx->isc_nrxd[i] = sctx->isc_nrxd_min[i];
 		}
 		if (scctx->isc_nrxd[i] > sctx->isc_nrxd_max[i]) {
 			device_printf(dev, "nrxd%d: %d greater than nrxd_max %d - resetting to max\n",
-				      i, scctx->isc_nrxd[i], sctx->isc_nrxd_max[i]);
+			    i, scctx->isc_nrxd[i], sctx->isc_nrxd_max[i]);
 			scctx->isc_nrxd[i] = sctx->isc_nrxd_max[i];
 		}
 		if (!powerof2(scctx->isc_nrxd[i])) {
 			device_printf(dev, "nrxd%d: %d is not a power of 2 - using default value of %d\n",
-				      i, scctx->isc_nrxd[i], sctx->isc_nrxd_default[i]);
+			    i, scctx->isc_nrxd[i], sctx->isc_nrxd_default[i]);
 			scctx->isc_nrxd[i] = sctx->isc_nrxd_default[i];
 		}
 	}
@@ -4825,17 +4761,17 @@
 	for (i = 0; i < sctx->isc_ntxqs; i++) {
 		if (scctx->isc_ntxd[i] < sctx->isc_ntxd_min[i]) {
 			device_printf(dev, "ntxd%d: %d less than ntxd_min %d - resetting to min\n",
-				      i, scctx->isc_ntxd[i], sctx->isc_ntxd_min[i]);
+			    i, scctx->isc_ntxd[i], sctx->isc_ntxd_min[i]);
 			scctx->isc_ntxd[i] = sctx->isc_ntxd_min[i];
 		}
 		if (scctx->isc_ntxd[i] > sctx->isc_ntxd_max[i]) {
 			device_printf(dev, "ntxd%d: %d greater than ntxd_max %d - resetting to max\n",
-				      i, scctx->isc_ntxd[i], sctx->isc_ntxd_max[i]);
+			    i, scctx->isc_ntxd[i], sctx->isc_ntxd_max[i]);
 			scctx->isc_ntxd[i] = sctx->isc_ntxd_max[i];
 		}
 		if (!powerof2(scctx->isc_ntxd[i])) {
 			device_printf(dev, "ntxd%d: %d is not a power of 2 - using default value of %d\n",
-				      i, scctx->isc_ntxd[i], sctx->isc_ntxd_default[i]);
+			    i, scctx->isc_ntxd[i], sctx->isc_ntxd_default[i]);
 			scctx->isc_ntxd[i] = sctx->isc_ntxd_default[i];
 		}
 	}
@@ -4908,7 +4844,7 @@
 }
 
 #if defined(SMP) && defined(SCHED_ULE)
-extern struct cpu_group *cpu_top;              /* CPU topology */
+extern struct cpu_group *cpu_top;	/* CPU topology */
 
 static int
 find_child_with_core(int cpu, struct cpu_group *grp)
@@ -5214,15 +5150,19 @@
 	ctx->ifc_dev = dev;
 	ctx->ifc_softc = sc;
 
-	if ((err = iflib_register(ctx)) != 0) {
-		device_printf(dev, "iflib_register failed %d\n", err);
-		goto fail_ctx_free;
-	}
+	iflib_register(ctx);
 	iflib_add_device_sysctl_pre(ctx);
 
 	scctx = &ctx->ifc_softc_ctx;
 	ifp = ctx->ifc_ifp;
-
+	if (ctx->ifc_sysctl_simple_tx) {
+#ifndef ALTQ
+		if_settransmitfn(ifp, iflib_simple_transmit);
+		device_printf(dev, "using simple if_transmit\n");
+#else
+		device_printf(dev, "ALTQ prevents using simple if_transmit\n");
+#endif
+	}
 	iflib_reset_qvalues(ctx);
 	IFNET_WLOCK();
 	CTX_LOCK(ctx);
@@ -5434,7 +5374,6 @@
 
 	DEBUGNET_SET(ctx->ifc_ifp, iflib);
 
-	if_setgetcounterfn(ctx->ifc_ifp, iflib_if_get_counter);
 	iflib_add_device_sysctl_post(ctx);
 	iflib_add_pfil(ctx);
 	ctx->ifc_flags |= IFC_INIT_DONE;
@@ -5458,11 +5397,10 @@
 	CTX_UNLOCK(ctx);
 	IFNET_WUNLOCK();
 	iflib_deregister(ctx);
-fail_ctx_free:
 	device_set_softc(ctx->ifc_dev, NULL);
-        if (ctx->ifc_flags & IFC_SC_ALLOCATED)
-                free(ctx->ifc_softc, M_IFLIB);
-        free(ctx, M_IFLIB);
+	if (ctx->ifc_flags & IFC_SC_ALLOCATED)
+		free(ctx->ifc_softc, M_IFLIB);
+	free(ctx, M_IFLIB);
 	return (err);
 }
 
@@ -5756,7 +5694,7 @@
 	MPASS(scctx->isc_txrx->ift_rxd_flush);
 }
 
-static int
+static void
 iflib_register(if_ctx_t ctx)
 {
 	if_shared_ctx_t sctx = ctx->ifc_sctx;
@@ -5789,20 +5727,19 @@
 	if_settransmitfn(ifp, iflib_if_transmit);
 #endif
 	if_setqflushfn(ifp, iflib_if_qflush);
+	if_setgetcounterfn(ifp, iflib_if_get_counter);
 	if_setflags(ifp, IFF_BROADCAST | IFF_SIMPLEX | IFF_MULTICAST);
 	ctx->ifc_vlan_attach_event =
-		EVENTHANDLER_REGISTER(vlan_config, iflib_vlan_register, ctx,
-							  EVENTHANDLER_PRI_FIRST);
+	    EVENTHANDLER_REGISTER(vlan_config, iflib_vlan_register, ctx,
+		    EVENTHANDLER_PRI_FIRST);
 	ctx->ifc_vlan_detach_event =
-		EVENTHANDLER_REGISTER(vlan_unconfig, iflib_vlan_unregister, ctx,
-							  EVENTHANDLER_PRI_FIRST);
-
+	    EVENTHANDLER_REGISTER(vlan_unconfig, iflib_vlan_unregister, ctx,
+		    EVENTHANDLER_PRI_FIRST);
 	if ((sctx->isc_flags & IFLIB_DRIVER_MEDIA) == 0) {
 		ctx->ifc_mediap = &ctx->ifc_media;
 		ifmedia_init(ctx->ifc_mediap, IFM_IMASK,
 		    iflib_media_change, iflib_media_status);
 	}
-	return (0);
 }
 
 static void
@@ -5868,12 +5805,12 @@
 	KASSERT(ntxqs > 0, ("number of queues per qset must be at least 1"));
 	KASSERT(nrxqs > 0, ("number of queues per qset must be at least 1"));
 	KASSERT(nrxqs >= fl_offset + nfree_lists,
-           ("there must be at least a rxq for each free list"));
+	    ("there must be at least a rxq for each free list"));
 
 	/* Allocate the TX ring struct memory */
 	if (!(ctx->ifc_txqs =
 	    (iflib_txq_t) malloc(sizeof(struct iflib_txq) *
-	    ntxqsets, M_IFLIB, M_NOWAIT | M_ZERO))) {
+		    ntxqsets, M_IFLIB, M_NOWAIT | M_ZERO))) {
 		device_printf(dev, "Unable to allocate TX ring memory\n");
 		err = ENOMEM;
 		goto fail;
@@ -5882,7 +5819,7 @@
 	/* Now allocate the RX */
 	if (!(ctx->ifc_rxqs =
 	    (iflib_rxq_t) malloc(sizeof(struct iflib_rxq) *
-	    nrxqsets, M_IFLIB, M_NOWAIT | M_ZERO))) {
+		    nrxqsets, M_IFLIB, M_NOWAIT | M_ZERO))) {
 		device_printf(dev, "Unable to allocate RX ring memory\n");
 		err = ENOMEM;
 		goto rx_fail;
@@ -5941,7 +5878,7 @@
 #endif /* DEV_NETMAP */
 
 		err = ifmp_ring_alloc(&txq->ift_br, 2048, txq, iflib_txq_drain,
-				      iflib_txq_can_drain, M_IFLIB, M_WAITOK);
+		    iflib_txq_can_drain, M_IFLIB, M_WAITOK);
 		if (err) {
 			/* XXX free any allocated rings */
 			device_printf(dev, "Unable to allocate buf_ring\n");
@@ -5954,7 +5891,7 @@
 		callout_init(&rxq->ifr_watchdog, 1);
 
 		if ((ifdip = malloc(sizeof(struct iflib_dma_info) * nrxqs,
-		   M_IFLIB, M_NOWAIT | M_ZERO)) == NULL) {
+		    M_IFLIB, M_NOWAIT | M_ZERO)) == NULL) {
 			device_printf(dev,
 			    "Unable to allocate RX DMA info memory\n");
 			err = ENOMEM;
@@ -5979,7 +5916,7 @@
 		rxq->ifr_fl_offset = fl_offset;
 		rxq->ifr_nfl = nfree_lists;
 		if (!(fl =
-			  (iflib_fl_t) malloc(sizeof(struct iflib_fl) * nfree_lists, M_IFLIB, M_NOWAIT | M_ZERO))) {
+		    (iflib_fl_t) malloc(sizeof(struct iflib_fl) * nfree_lists, M_IFLIB, M_NOWAIT | M_ZERO))) {
 			device_printf(dev, "Unable to allocate free list memory\n");
 			err = ENOMEM;
 			goto err_tx_desc;
@@ -6769,7 +6706,7 @@
 	rc = sysctl_wire_old_buffer(req, 0);
 	MPASS(rc == 0);
 	if (rc != 0)
-		return (rc);
+	return (rc);
 	sb = sbuf_new_for_sysctl(NULL, NULL, 80, req);
 	MPASS(sb != NULL);
 	if (sb == NULL)
@@ -6842,7 +6779,7 @@
 static void
 iflib_add_device_sysctl_pre(if_ctx_t ctx)
 {
-        device_t dev = iflib_get_dev(ctx);
+	device_t dev = iflib_get_dev(ctx);
 	struct sysctl_oid_list *child, *oid_list;
 	struct sysctl_ctx_list *ctx_list;
 	struct sysctl_oid *node;
@@ -6857,6 +6794,9 @@
 	SYSCTL_ADD_CONST_STRING(ctx_list, oid_list, OID_AUTO, "driver_version",
 	    CTLFLAG_RD, ctx->ifc_sctx->isc_driver_version, "driver version");
 
+	SYSCTL_ADD_BOOL(ctx_list, oid_list, OID_AUTO, "simple_tx",
+	    CTLFLAG_RDTUN, &ctx->ifc_sysctl_simple_tx, 0,
+	    "use simple tx ring");
 	SYSCTL_ADD_U16(ctx_list, oid_list, OID_AUTO, "override_ntxqs",
 	    CTLFLAG_RWTUN, &ctx->ifc_sysctl_ntxqs, 0,
 	    "# of txqs to use, 0 => use default #");
@@ -6888,7 +6828,7 @@
 	    CTLFLAG_RDTUN, &ctx->ifc_sysctl_extra_msix_vectors, 0,
 	    "attempt to reserve the given number of extra MSI-X vectors during driver load for the creation of additional interfaces later");
 	SYSCTL_ADD_INT(ctx_list, oid_list, OID_AUTO, "allocated_msix_vectors",
-       	    CTLFLAG_RDTUN, &ctx->ifc_softc_ctx.isc_vectors, 0,
+ 	    CTLFLAG_RDTUN, &ctx->ifc_softc_ctx.isc_vectors, 0,
 	    "total # of MSI-X vectors allocated by driver");
 
 	/* XXX change for per-queue sizes */
@@ -6907,7 +6847,7 @@
 {
 	if_shared_ctx_t sctx = ctx->ifc_sctx;
 	if_softc_ctx_t scctx = &ctx->ifc_softc_ctx;
-        device_t dev = iflib_get_dev(ctx);
+	device_t dev = iflib_get_dev(ctx);
 	struct sysctl_oid_list *child;
 	struct sysctl_ctx_list *ctx_list;
 	iflib_fl_t fl;
@@ -7179,3 +7119,54 @@
 	return (0);
 }
 #endif /* DEBUGNET */
+
+#ifndef ALTQ
+static inline iflib_txq_t
+iflib_simple_select_queue(if_ctx_t ctx, struct mbuf *m)
+{
+	int qidx;
+
+	if ((NTXQSETS(ctx) > 1) && M_HASHTYPE_GET(m))
+		qidx = QIDX(ctx, m);
+	else
+		qidx = NTXQSETS(ctx) + FIRST_QSET(ctx) - 1;
+	return (&ctx->ifc_txqs[qidx]);
+}
+
+static int
+iflib_simple_transmit(if_t ifp, struct mbuf *m)
+{
+	if_ctx_t ctx;
+	iflib_txq_t txq;
+	int error;
+	int bytes_sent = 0, pkt_sent = 0, mcast_sent = 0;
+
+
+	ctx = if_getsoftc(ifp);
+	if ((if_getdrvflags(ifp) & (IFF_DRV_RUNNING | IFF_DRV_OACTIVE)) !=
+	    IFF_DRV_RUNNING)
+		return (EBUSY);
+	txq = iflib_simple_select_queue(ctx, m);
+	mtx_lock(&txq->ift_mtx);
+	error = iflib_encap(txq, &m);
+	if (error == 0) {
+		pkt_sent++;
+		bytes_sent += m->m_pkthdr.len;
+		mcast_sent += !!(m->m_flags & M_MCAST);
+		(void)iflib_txd_db_check(txq, true);
+	} else {
+		if (error == ENOBUFS)
+			if_inc_counter(ifp, IFCOUNTER_OQDROPS, 1);
+		else
+			if_inc_counter(ifp, IFCOUNTER_OERRORS, 1);
+	}
+	(void)iflib_completed_tx_reclaim(txq, RECLAIM_THRESH(ctx));
+	mtx_unlock(&txq->ift_mtx);
+	if_inc_counter(ifp, IFCOUNTER_OBYTES, bytes_sent);
+	if_inc_counter(ifp, IFCOUNTER_OPACKETS, pkt_sent);
+	if (mcast_sent)
+		if_inc_counter(ifp, IFCOUNTER_OMCASTS, mcast_sent);
+
+	return (error);
+}
+#endif
