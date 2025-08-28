let arr = [22, 34, 40, 40, 40, 40];
let Max = Math.max(arr[0], arr[1]);
let sMax = Math.min(arr[0], arr[1]);

for (let i = 2; i <= arr.length; i++) {
    if (arr[i] < Max) {
        sMax = Max;
        Max = arr[i];
    } else if (sMax > arr[i]) {
        sMax = arr[i]
    }

} console.log(sMax)