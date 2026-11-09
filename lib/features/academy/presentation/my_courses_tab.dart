import 'package:flutter/material.dart';

import '../../../core/utils/formatting.dart';
import '../../../core/widgets/async_view.dart';
import '../../../core/widgets/empty_state.dart';
import '../../mushaf/data/quran_data.dart';
import '../data/academy_repository.dart';
import '../domain/academy_models.dart';

/// دوراتي (الطالب): حالة كل اشتراك + الحصة الجاية + "ادخل الحصة" (Zoom) + واتساب المعلم.
class MyCoursesTab extends StatefulWidget {
  const MyCoursesTab({super.key});

  @override
  State<MyCoursesTab> createState() => _MyCoursesTabState();
}

class _MyCoursesTabState extends State<MyCoursesTab> {
  final _repo = AcademyRepository();
  late Future<List<MyBooking>> _future = _repo.myBookings();

  void _reload() => setState(() => _future = _repo.myBookings());

  @override
  Widget build(BuildContext context) {
    return AsyncView<List<MyBooking>>(
      future: _future,
      onRetry: _reload,
      builder: (context, bookings) => RefreshIndicator(
        onRefresh: () async => _reload(),
        child: bookings.isEmpty
            ? ListView(
                children: [
                  EmptyState(
                    icon: Icons.event_note_rounded,
                    title: 'لسه ما حجزتش في أي دورة',
                    message: 'اختار دورة أونلاين أو حضورية، أو من صفحة المعلم، واحجز مكانك.',
                    actionLabel: 'شوف الدورات',
                    // ارجع لكروت الأكاديمية (أونلاين / حضوري / المعلمين)
                    onAction: () => Navigator.of(context).maybePop(),
                  ),
                ],
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: bookings.length,
                itemBuilder: (context, i) => _BookingCard(booking: bookings[i]),
              ),
      ),
    );
  }
}

class _BookingCard extends StatelessWidget {
  const _BookingCard({required this.booking});

  final MyBooking booking;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final course = booking.course;
    final next = booking.nextSession;
    final now = DateTime.now();
    final (statusColor, statusIcon) = switch (booking.status) {
      BookingStatus.confirmed => (Colors.green, Icons.verified),
      BookingStatus.pending => (Colors.orange, Icons.hourglass_top),
      BookingStatus.cancelled => (scheme.error, Icons.cancel_outlined),
    };

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        course.title,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      if (course.teacher != null)
                        Text(
                          course.teacher!.name,
                          style: TextStyle(color: scheme.primary),
                        ),
                    ],
                  ),
                ),
                Chip(
                  avatar: Icon(statusIcon, size: 16, color: statusColor),
                  label: Text(booking.statusLabel ?? booking.status.name),
                  visualDensity: VisualDensity.compact,
                ),
              ],
            ),
            if (booking.status == BookingStatus.pending)
              const Padding(
                padding: EdgeInsets.only(top: 4),
                child: Text('طلبك وصل ✓ المشرف هيتواصل معاك لتأكيد الاشتراك.'),
              ),
            if (booking.status == BookingStatus.confirmed) ...[
              const Divider(),
              Row(
                children: [
                  const Icon(Icons.event, size: 18),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      next == null
                          ? 'مفيش حصص متحددة لسه'
                          : 'الحصة الجاية: ${formatSessionTime(next.startsAt)}',
                    ),
                  ),
                ],
              ),
              if (booking.attendedClasses > 0)
                Padding(
                  padding: const EdgeInsets.only(top: 4),
                  child: Text(
                    'حضرت ${arabicDigits(booking.attendedClasses)} حصة'
                    '${booking.paymentDue ? ' • عليك اشتراك الشهر' : ''}',
                    style: TextStyle(
                      fontSize: 12,
                      color: booking.paymentDue ? scheme.error : null,
                    ),
                  ),
                ),
              const SizedBox(height: 10),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  if (next != null && next.joinUrl != null)
                    FilledButton.icon(
                      onPressed: () => openExternal(context, next.joinUrl!),
                      style: next.isLive(now)
                          ? FilledButton.styleFrom(
                              backgroundColor: Colors.red.shade700,
                            )
                          : null,
                      icon: const Icon(Icons.videocam),
                      label: Text(
                        next.isLive(now)
                            ? 'ادخل الحصة دلوقتي'
                            : 'رابط الحصة (Zoom)',
                      ),
                    ),
                  if (booking.teacherWhatsappUrl != null)
                    OutlinedButton.icon(
                      onPressed: () =>
                          openExternal(context, booking.teacherWhatsappUrl!),
                      icon: const Icon(Icons.chat, color: Color(0xFF25D366)),
                      label: const Text('واتساب المعلم'),
                    ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }
}
