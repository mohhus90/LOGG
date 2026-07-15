import 'package:geolocator/geolocator.dart';

class LocationResult {
  final double? latitude;
  final double? longitude;
  final String? error;

  LocationResult({this.latitude, this.longitude, this.error});

  bool get hasCoordinates => latitude != null && longitude != null;
}

class LocationService {
  static Future<LocationResult> getCurrentPosition() async {
    if (!await Geolocator.isLocationServiceEnabled()) {
      return LocationResult(error: 'خدمة تحديد الموقع غير مفعّلة على الجهاز');
    }

    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }

    if (permission == LocationPermission.denied) {
      return LocationResult(error: 'تم رفض إذن الوصول للموقع');
    }
    if (permission == LocationPermission.deniedForever) {
      return LocationResult(error: 'إذن الموقع مرفوض بشكل دائم، فعّله من إعدادات الجهاز');
    }

    try {
      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: Duration(seconds: 20),
        ),
      );
      return LocationResult(latitude: position.latitude, longitude: position.longitude);
    } catch (e) {
      return LocationResult(error: 'تعذر تحديد الموقع الحالي');
    }
  }
}
