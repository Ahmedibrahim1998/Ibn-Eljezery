import 'package:flutter/material.dart';

import '../network/api_client.dart';
import 'empty_state.dart';

/// بيعرض Future: تحميل ← خطأ مع زرار "حاول تاني" ← البيانات.
class AsyncView<T> extends StatelessWidget {
  const AsyncView({
    super.key,
    required this.future,
    required this.builder,
    required this.onRetry,
  });

  final Future<T> future;
  final Widget Function(BuildContext context, T data) builder;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<T>(
      future: future,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snapshot.hasError) {
          final error = snapshot.error;
          final offline = error is ApiException && error.statusCode == null;
          return EmptyState(
            icon: offline
                ? Icons.wifi_off_rounded
                : Icons.error_outline_rounded,
            title: offline ? 'مفيش اتصال' : 'حصلت مشكلة',
            message: '$error',
            actionLabel: 'حاول تاني',
            actionIcon: Icons.refresh_rounded,
            onAction: onRetry,
          );
        }
        return builder(context, snapshot.data as T);
      },
    );
  }
}
